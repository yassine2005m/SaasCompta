import os
import sys
import time
import json
import logging
import threading
import configparser
import urllib.request
import urllib.error
import subprocess
from datetime import datetime

# ==============================================================================
# SAGE CLOUD AGENT - Universal Invest Strategy
# ==============================================================================
# Agent autonome qui se connecte au site web hébergé et synchronise
# automatiquement les données comptables vers Sage 100 en local.
# ==============================================================================

# --- LOGGING ---
LOG_DIR = os.path.join(os.path.dirname(os.path.abspath(sys.argv[0])), "logs")
os.makedirs(LOG_DIR, exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s - %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S',
    handlers=[
        logging.FileHandler(os.path.join(LOG_DIR, f"sage_sync_{datetime.now().strftime('%Y%m%d')}.log"), encoding='utf-8'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger("SageCloudAgent")

# --- CONFIGURATION ---
def load_config():
    """Charge la configuration depuis config.ini"""
    config = configparser.ConfigParser()
    config_path = os.path.join(os.path.dirname(os.path.abspath(sys.argv[0])), "config.ini")
    
    if not os.path.exists(config_path):
        # Créer un config.ini par défaut
        config['server'] = {
            'url': 'http://VOTRE-SITE.infinityfreeapp.com',
            'token': 'sage_sync_protected_token_2026'
        }
        config['sage'] = {
            'import_folder': r'C:\Sage_Import',
            'format_file': r'C:\Sage_Import\FORMAT_UIS.ema',
            'company_file': r'C:\Users\pc\Desktop\UIS2026.mae',
            'exe_path': r'C:\Program Files (x86)\Sage\iComptabilité\Maestria.exe'
        }
        config['sync'] = {
            'interval_minutes': '5',
            'auto_start': 'true'
        }
        with open(config_path, 'w') as f:
            config.write(f)
        logger.info(f"Fichier config.ini créé dans : {config_path}")
        logger.info("Veuillez le modifier avec l'URL de votre site, puis relancez l'agent.")
        return None
    
    config.read(config_path, encoding='utf-8')
    return config


def is_sage_running():
    """Vérifie si Sage 100 (Maestria.exe) est en cours d'exécution."""
    try:
        output = subprocess.check_output(
            'tasklist /FI "IMAGENAME eq Maestria.exe"', 
            shell=True
        ).decode('cp1252', errors='ignore')
        return "Maestria.exe" in output
    except Exception:
        return False


def push_via_vbs(file_path, format_ema):
    """Injection dans Sage via simulation UI (VBScript)."""
    import_dir = os.path.dirname(file_path)
    vbs_path = os.path.join(import_dir, "cloud_pusher.vbs")
    
    vbs_content = f'''
    Set WshShell = WScript.CreateObject("WScript.Shell")
    If WshShell.AppActivate("Sage 100") Then
        WScript.Sleep 200
        WshShell.SendKeys "%f"
        WScript.Sleep 200
        WshShell.SendKeys "i"
        WScript.Sleep 200
        WshShell.SendKeys "p"
        WScript.Sleep 1000
        WshShell.SendKeys "^a"
        WScript.Sleep 100
        WshShell.SendKeys "{{BACKSPACE}}"
        WScript.Sleep 200
        WshShell.SendKeys "{format_ema}"
        WScript.Sleep 200
        WshShell.SendKeys "{{ENTER}}"
        WScript.Sleep 500
        WshShell.SendKeys "^a"
        WScript.Sleep 100
        WshShell.SendKeys "{{BACKSPACE}}"
        WScript.Sleep 200
        WshShell.SendKeys "{file_path}"
        WScript.Sleep 200
        WshShell.SendKeys "{{ENTER}}"
        WScript.Sleep 500
        WshShell.SendKeys "{{ENTER}}"
    Else
        WScript.Quit 1
    End If
    '''
    
    with open(vbs_path, "w", encoding="cp1252") as f:
        f.write(vbs_content)
    
    try:
        subprocess.run(
            ["cscript.exe", "//Nologo", vbs_path], 
            check=True, 
            creationflags=subprocess.CREATE_NO_WINDOW
        )
        return True
    except subprocess.CalledProcessError:
        return False


def push_to_sage(file_path, config):
    """
    Stratégie d'injection Sage :
    1. Si Sage est fermé → Import via ligne de commande (-I)
    2. Si Sage est ouvert → Simulation UI via VBScript
    """
    sage_exe = config.get('sage', 'exe_path', fallback='')
    company_file = config.get('sage', 'company_file', fallback='')
    format_file = config.get('sage', 'format_file', fallback='')
    
    # Méthode 1 : Sage fermé → Import direct (silencieux)
    if not is_sage_running():
        if os.path.exists(sage_exe) and os.path.exists(company_file):
            try:
                logger.info("Sage fermé → Importation directe en arrière-plan...")
                cmd = [sage_exe, company_file, "-I", format_file, file_path]
                subprocess.Popen(cmd)
                return True
            except Exception as e:
                logger.warning(f"Import direct échoué : {e}")
    
    # Méthode 2 : Sage ouvert → Simulation UI
    if is_sage_running():
        logger.info("Sage ouvert → Injection via simulation UI...")
        return push_via_vbs(file_path, format_file)
    
    # Sage n'est ni ouvert ni trouvé
    logger.warning("Sage n'est pas disponible. Fichier sauvegardé pour import manuel.")
    return False


def fetch_and_sync(config):
    """
    Récupère les données depuis le site web hébergé et les injecte dans Sage.
    """
    url = config.get('server', 'url', fallback='').rstrip('/')
    token = config.get('server', 'token', fallback='')
    import_folder = config.get('sage', 'import_folder', fallback=r'C:\Sage_Import')
    
    if not url or url == 'http://VOTRE-SITE.infinityfreeapp.com':
        logger.error("URL du site non configurée ! Modifiez config.ini")
        return False
    
    os.makedirs(import_folder, exist_ok=True)
    
    api_url = f"{url}/api/sage-sync?token={token}"
    
    try:
        req = urllib.request.Request(api_url, headers={
            'User-Agent': 'SageCloudAgent/2.0',
            'Accept': 'text/plain'
        })
        
        with urllib.request.urlopen(req, timeout=30) as response:
            if response.status == 200:
                data = response.read()
                
                if not data or len(data.strip()) == 0:
                    logger.info("✓ Aucune nouvelle donnée à synchroniser.")
                    return True
                
                # Sauvegarder le fichier
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                file_path = os.path.join(import_folder, f"CLOUD_SYNC_{timestamp}.txt")
                
                with open(file_path, "wb") as f:
                    f.write(data)
                
                logger.info(f"✓ Données téléchargées ({len(data)} octets) → {file_path}")
                
                # Tenter l'injection dans Sage
                if push_to_sage(file_path, config):
                    logger.info("✅ Synchronisation RÉUSSIE ! Données injectées dans Sage.")
                    
                    # Confirmer au serveur
                    try:
                        ack_url = f"{url}/api/sage-sync/ack?token={token}&entries={len(data)}"
                        ack_req = urllib.request.Request(ack_url, method='POST', 
                                                        headers={'User-Agent': 'SageCloudAgent/2.0'})
                        urllib.request.urlopen(ack_req, timeout=10)
                    except Exception:
                        pass  # L'acquittement n'est pas critique
                    
                    return True
                else:
                    logger.warning("⚠ Fichier sauvegardé mais Sage non disponible.")
                    logger.info(f"   → Le fichier est dans : {file_path}")
                    logger.info("   → Vous pouvez l'importer manuellement dans Sage.")
                    return True  # Le fichier est quand même là
            else:
                logger.error(f"Erreur API : Code {response.status}")
                return False
                
    except urllib.error.HTTPError as e:
        if e.code == 401:
            logger.error("❌ Token invalide ! Vérifiez le token dans config.ini")
        else:
            logger.error(f"Erreur HTTP {e.code}: {e.reason}")
        return False
    except urllib.error.URLError as e:
        logger.error(f"❌ Impossible de contacter le serveur : {e.reason}")
        logger.info("   → Vérifiez votre connexion internet")
        logger.info(f"   → Vérifiez l'URL dans config.ini : {url}")
        return False
    except Exception as e:
        logger.error(f"Erreur inattendue : {str(e)}")
        return False


def cleanup_old_files(import_folder, max_files=20):
    """Supprime les anciens fichiers de sync pour ne pas encombrer."""
    try:
        files = sorted(
            [f for f in os.listdir(import_folder) if f.startswith("CLOUD_SYNC_") and f.endswith(".txt")],
            key=lambda x: os.path.getmtime(os.path.join(import_folder, x))
        )
        while len(files) > max_files:
            old_file = files.pop(0)
            os.remove(os.path.join(import_folder, old_file))
            logger.info(f"Nettoyage : {old_file} supprimé")
    except Exception:
        pass


# ==============================================================================
# INTERFACE GRAPHIQUE (Tkinter)
# ==============================================================================

try:
    import tkinter as tk
    from tkinter import ttk, messagebox
    HAS_GUI = True
except ImportError:
    HAS_GUI = False


class SageCloudAgentGUI:
    """Interface graphique moderne pour l'agent de synchronisation."""
    
    def __init__(self, root, config):
        self.root = root
        self.config = config
        self.is_running = False
        self.sync_thread = None
        self.sync_count = 0
        self.last_sync = "Jamais"
        
        # Fenêtre
        self.root.title("Sage Cloud Agent - Universal Invest Strategy")
        self.root.geometry("520x420")
        self.root.resizable(False, False)
        self.root.configure(bg='#1e1e2e')
        
        # Essayer de définir l'icône
        try:
            self.root.iconbitmap(default='')
        except:
            pass
        
        # Style
        style = ttk.Style()
        style.theme_use('clam')
        style.configure('Title.TLabel', font=('Segoe UI', 16, 'bold'), 
                       foreground='#cdd6f4', background='#1e1e2e')
        style.configure('Info.TLabel', font=('Segoe UI', 10), 
                       foreground='#a6adc8', background='#1e1e2e')
        style.configure('Status.TLabel', font=('Segoe UI', 11, 'bold'), 
                       foreground='#a6e3a1', background='#1e1e2e')
        style.configure('URL.TLabel', font=('Consolas', 9), 
                       foreground='#89b4fa', background='#1e1e2e')
        style.configure('Green.TButton', font=('Segoe UI', 11, 'bold'))
        style.configure('Red.TButton', font=('Segoe UI', 11, 'bold'))
        
        # Header
        header_frame = tk.Frame(root, bg='#1e1e2e', pady=15)
        header_frame.pack(fill='x')
        
        ttk.Label(header_frame, text="☁️  Sage Cloud Agent", style='Title.TLabel').pack()
        ttk.Label(header_frame, text="Universal Invest Strategy", style='Info.TLabel').pack()
        
        # Separator
        tk.Frame(root, bg='#313244', height=2).pack(fill='x', padx=20)
        
        # Info Frame
        info_frame = tk.Frame(root, bg='#1e1e2e', pady=15, padx=25)
        info_frame.pack(fill='x')
        
        url = self.config.get('server', 'url', fallback='Non configuré')
        interval = self.config.get('sync', 'interval_minutes', fallback='5')
        
        ttk.Label(info_frame, text=f"Serveur : {url}", style='URL.TLabel').pack(anchor='w', pady=2)
        ttk.Label(info_frame, text=f"Intervalle : toutes les {interval} minutes", style='Info.TLabel').pack(anchor='w', pady=2)
        
        # Status Frame
        status_frame = tk.Frame(root, bg='#1e1e2e', pady=10, padx=25)
        status_frame.pack(fill='x')
        
        self.status_var = tk.StringVar(value="⏸ En attente...")
        self.status_label = ttk.Label(status_frame, textvariable=self.status_var, style='Status.TLabel')
        self.status_label.pack(anchor='w')
        
        self.last_sync_var = tk.StringVar(value="Dernière sync : Jamais")
        ttk.Label(status_frame, textvariable=self.last_sync_var, style='Info.TLabel').pack(anchor='w', pady=2)
        
        self.count_var = tk.StringVar(value="Synchronisations effectuées : 0")
        ttk.Label(status_frame, textvariable=self.count_var, style='Info.TLabel').pack(anchor='w', pady=2)
        
        # Separator
        tk.Frame(root, bg='#313244', height=2).pack(fill='x', padx=20, pady=5)
        
        # Buttons Frame
        btn_frame = tk.Frame(root, bg='#1e1e2e', pady=15)
        btn_frame.pack(fill='x', padx=25)
        
        self.start_btn = tk.Button(
            btn_frame, text="▶  DÉMARRER", font=('Segoe UI', 12, 'bold'),
            bg='#a6e3a1', fg='#1e1e2e', activebackground='#94e2d5',
            relief='flat', cursor='hand2', padx=20, pady=8,
            command=self.toggle_sync
        )
        self.start_btn.pack(fill='x', pady=5)
        
        self.sync_now_btn = tk.Button(
            btn_frame, text="🔄  Synchroniser Maintenant", font=('Segoe UI', 10),
            bg='#89b4fa', fg='#1e1e2e', activebackground='#74c7ec',
            relief='flat', cursor='hand2', padx=15, pady=5,
            command=self.manual_sync
        )
        self.sync_now_btn.pack(fill='x', pady=5)
        
        # Log area
        self.log_text = tk.Text(root, height=3, bg='#11111b', fg='#cdd6f4', 
                               font=('Consolas', 8), relief='flat', padx=5, pady=5)
        self.log_text.pack(fill='x', padx=25, pady=(5, 15))
        self.log_text.insert('end', "Prêt. Cliquez sur DÉMARRER pour lancer la synchronisation.\n")
        self.log_text.config(state='disabled')
        
        # Auto-start si configuré
        if self.config.get('sync', 'auto_start', fallback='false').lower() == 'true':
            self.root.after(1000, self.toggle_sync)
        
        # Fermeture propre
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
    
    def add_log(self, message):
        """Ajoute un message dans la zone de log."""
        self.log_text.config(state='normal')
        timestamp = datetime.now().strftime("%H:%M:%S")
        self.log_text.insert('end', f"[{timestamp}] {message}\n")
        self.log_text.see('end')
        # Garder seulement les 50 dernières lignes
        lines = int(self.log_text.index('end-1c').split('.')[0])
        if lines > 50:
            self.log_text.delete('1.0', '2.0')
        self.log_text.config(state='disabled')
    
    def toggle_sync(self):
        """Démarre ou arrête la synchronisation automatique."""
        if self.is_running:
            self.is_running = False
            self.start_btn.config(text="▶  DÉMARRER", bg='#a6e3a1')
            self.status_var.set("⏸ Synchronisation arrêtée")
            self.add_log("Synchronisation automatique arrêtée.")
        else:
            self.is_running = True
            self.start_btn.config(text="⏹  ARRÊTER", bg='#f38ba8')
            self.status_var.set("🔄 Synchronisation en cours...")
            self.add_log("Synchronisation automatique démarrée !")
            self.sync_loop()
    
    def sync_loop(self):
        """Boucle de synchronisation automatique."""
        if not self.is_running:
            return
        
        # Lancer la sync dans un thread pour ne pas bloquer l'UI
        thread = threading.Thread(target=self._do_sync, daemon=True)
        thread.start()
        
        # Planifier la prochaine exécution
        interval = int(self.config.get('sync', 'interval_minutes', fallback='5'))
        self.root.after(interval * 60 * 1000, self.sync_loop)
    
    def manual_sync(self):
        """Synchronisation manuelle unique."""
        self.add_log("Synchronisation manuelle lancée...")
        thread = threading.Thread(target=self._do_sync, daemon=True)
        thread.start()
    
    def _do_sync(self):
        """Exécute la synchronisation (thread-safe)."""
        try:
            self.root.after(0, lambda: self.status_var.set("🔄 Connexion au serveur..."))
            
            success = fetch_and_sync(self.config)
            
            now = datetime.now().strftime("%H:%M:%S")
            if success:
                self.sync_count += 1
                self.last_sync = now
                self.root.after(0, lambda: self.status_var.set("✅ Dernière sync réussie"))
                self.root.after(0, lambda: self.add_log(f"✓ Sync #{self.sync_count} réussie"))
            else:
                self.root.after(0, lambda: self.status_var.set("⚠ Erreur de synchronisation"))
                self.root.after(0, lambda: self.add_log("✗ Échec de la synchronisation"))
            
            self.root.after(0, lambda: self.last_sync_var.set(f"Dernière sync : {self.last_sync}"))
            self.root.after(0, lambda: self.count_var.set(f"Synchronisations effectuées : {self.sync_count}"))
            
            # Nettoyage des vieux fichiers
            import_folder = self.config.get('sage', 'import_folder', fallback=r'C:\Sage_Import')
            cleanup_old_files(import_folder)
            
        except Exception as e:
            self.root.after(0, lambda: self.add_log(f"Erreur : {str(e)}"))
    
    def on_close(self):
        """Fermeture propre de l'application."""
        self.is_running = False
        self.root.destroy()


# ==============================================================================
# POINT D'ENTRÉE
# ==============================================================================

def main():
    config = load_config()
    
    if config is None:
        print("\n" + "=" * 60)
        print("  PREMIER LANCEMENT - CONFIG.INI CRÉÉ")
        print("=" * 60)
        print("\nModifiez le fichier 'config.ini' avec :")
        print("  - L'URL de votre site web hébergé")
        print("  - Les chemins de votre installation Sage")
        print("\nPuis relancez cet agent.")
        print()
        input("Appuyez sur Entrée pour quitter...")
        return
    
    if HAS_GUI:
        root = tk.Tk()
        app = SageCloudAgentGUI(root, config)
        root.mainloop()
    else:
        # Mode console (fallback)
        print("=" * 60)
        print("  SAGE CLOUD AGENT - Mode Console")
        print("=" * 60)
        interval = int(config.get('sync', 'interval_minutes', fallback='5'))
        print(f"Synchronisation toutes les {interval} minutes...")
        print("Ctrl+C pour arrêter.\n")
        
        try:
            while True:
                fetch_and_sync(config)
                time.sleep(interval * 60)
        except KeyboardInterrupt:
            print("\nArrêt de l'agent.")


if __name__ == "__main__":
    main()
