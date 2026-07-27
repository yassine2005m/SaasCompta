<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universal Invest Strategy</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --sidebar-bg: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-active: #ffffff;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: #334155;
            margin: 0;
            overflow-x: hidden;
        }

        /* Layout Structure */
        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        #sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #fff;
            flex-shrink: 0;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 24px 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-decoration: none;
            display: block;
            letter-spacing: 0.5px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .sidebar-nav li {
            padding: 4px 16px;
        }

        .sidebar-nav a {
            color: var(--sidebar-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: rgba(255,255,255,0.08);
            color: var(--sidebar-active);
        }
        
        .sidebar-nav a.active {
            background-color: var(--primary-color);
        }
        .sidebar-nav a.active:hover {
            background-color: var(--primary-hover);
        }

        .sidebar-nav a i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            opacity: 0.8;
        }

        .sidebar-nav a.active i {
            opacity: 1;
        }

        /* Main Content */
        #page-content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            width: calc(100% - 260px);
            background-color: var(--bg-color);
        }

        .topbar {
            background: #fff;
            padding: 16px 32px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .main-content {
            padding: 32px;
            flex-grow: 1;
        }

        /* Card Styles */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            background-color: #fff;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            color: #1e293b;
        }
        
        .card-body {
            padding: 24px;
        }

        /* Button Styles */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 8px 16px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* Table Styles */
        .table {
            vertical-align: middle;
            margin-bottom: 0;
        }
        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #64748b;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 16px;
            background-color: #f8fafc;
            font-weight: 600;
        }
        .table tbody td {
            padding: 16px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: #334155;
            font-size: 0.95rem;
        }
        .form-control, .form-select {
            border-color: #cbd5e1;
            padding: 10px 14px;
            font-size: 0.95rem;
            border-radius: 6px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
    </style>
    <style>
        /* Modern Chatbot UI */
        .chatbot-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            font-family: 'Inter', sans-serif;
        }

        .chatbot-button {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
        }

        .chatbot-button:hover {
            transform: scale(1.1) rotate(5deg);
            background: var(--primary-hover);
        }

        .chatbot-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 380px;
            height: 500px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: none;
            flex-direction: column;
            overflow: hidden;
            transform-origin: bottom right;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
            transform: scale(0.8) translateY(20px);
        }

        .chatbot-window.active {
            display: flex;
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .chatbot-header {
            background: var(--primary-color);
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chatbot-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .chatbot-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            line-height: 1.4;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.bot {
            align-self: flex-start;
            background: #f1f5f9;
            color: #334155;
            border-bottom-left-radius: 2px;
        }

        .message.user {
            align-self: flex-end;
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 2px;
        }

        .chatbot-input {
            padding: 15px;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            gap: 10px;
            background: rgba(255,255,255,0.5);
        }

        .chatbot-input input {
            flex-grow: 1;
            border: 1px solid #e2e8f0;
            border-radius: 25px;
            padding: 8px 15px;
            outline: none;
            background: white;
            font-size: 0.9rem;
        }

        .chatbot-input button {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .chatbot-input button:hover {
            background: var(--primary-hover);
        }

        /* Suggested Questions */
        .chatbot-suggestions {
            padding: 10px 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            background: #f8fafc;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .suggestion-btn {
            background: white;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .suggestion-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .chatbot-window {
                width: 320px;
                height: 450px;
                right: -10px;
                bottom: 70px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Topbar -->
            <div class="topbar">
                <h4 class="mb-0 text-dark fw-bold fs-5">@yield('title', 'Tableau de bord')</h4>
                <div class="user-profile d-flex align-items-center">
                    <span class="me-3 fw-medium text-secondary" style="font-size: 0.9rem;">
                        {{ auth()->user()->name ?? 'Utilisateur' }}
                        ({{ auth()->user()?->isAdmin() ? 'Admin' : 'Client' }})
                    </span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=4f46e5&color=fff" alt="Utilisateur" class="rounded-circle" width="36" height="36">
                    <form action="{{ route('logout') }}" method="POST" class="ms-3">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Se deconnecter</button>
                    </form>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Chatbot HTML -->
    <div class="chatbot-container">
        <button class="chatbot-button" id="chatbot-toggle" title="Besoin d'aide ?">
            <i class="fas fa-comment-dots"></i>
        </button>
        <div class="chatbot-window" id="chatbot-window">
            <div class="chatbot-header">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-1 me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-robot text-primary" style="font-size: 14px;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Assistant Universal</h5>
                        <small style="opacity: 0.8; font-size: 0.7rem;">En ligne pour vous aider</small>
                    </div>
                </div>
                <button class="btn-close btn-close-white" style="font-size: 0.8rem;" id="chatbot-close"></button>
            </div>
            <div class="chatbot-messages" id="chatbot-messages">
                <div class="message bot">
                    Bonjour ! Je suis l'assistant Universal Invest Strategy. Comment puis-je vous aider aujourd'hui avec votre comptabilité ?
                </div>
            </div>
            <div class="chatbot-suggestions" id="chatbot-suggestions">
                <button class="suggestion-btn" onclick="sendSuggestedMsg('Comment créer un contrat ?')">Créer un contrat</button>
                <button class="suggestion-btn" onclick="sendSuggestedMsg('Comment renouveler un contrat ?')">Renouvellement</button>
                <button class="suggestion-btn" onclick="sendSuggestedMsg('Quel est le prix ?')">Tarifs</button>
                <button class="suggestion-btn" onclick="sendSuggestedMsg('Comment exporter vers Sage ?')">Export Sage</button>
                <button class="suggestion-btn" onclick="sendSuggestedMsg('Où télécharger le contrat PDF ?')">Télécharger PDF</button>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbot-input-field" placeholder="Écrivez votre message...">
                <button id="chatbot-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js for Dashboard Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Chatbot Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('chatbot-toggle');
            const closeBtn = document.getElementById('chatbot-close');
            const windowEl = document.getElementById('chatbot-window');
            const messagesContainer = document.getElementById('chatbot-messages');
            const inputField = document.getElementById('chatbot-input-field');
            const sendBtn = document.getElementById('chatbot-send-btn');

            // Toggle Chatbot
            toggleBtn.addEventListener('click', () => {
                windowEl.classList.toggle('active');
                if(windowEl.classList.contains('active')) {
                    inputField.focus();
                }
            });

            closeBtn.addEventListener('click', () => {
                windowEl.classList.remove('active');
            });

            // Send Message Function
            async function sendMessage(textOverride = null) {
                const text = textOverride || inputField.value.trim();
                if (text === '') return;

                // Add User Message
                addMessage(text, 'user');
                if (!textOverride) inputField.value = '';

                // Hide suggestions after first message to save space
                document.getElementById('chatbot-suggestions').style.display = 'none';

                // Add Typing Indicator
                const typingDiv = document.createElement('div');
                typingDiv.classList.add('message', 'bot', 'typing');
                typingDiv.innerHTML = '<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>';
                messagesContainer.appendChild(typingDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                try {
                    const response = await fetch('{{ route('chatbot.message') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: text })
                    });

                    const data = await response.json();
                    typingDiv.remove();
                    addMessage(data.response || "Désolé, je rencontre un problème.", 'bot');
                } catch (error) {
                    typingDiv.remove();
                    addMessage("Erreur réseau. Veuillez réessayer.", 'bot');
                    console.error('Chatbot Error:', error);
                }
            }

            function addMessage(text, type) {
                const msgDiv = document.createElement('div');
                msgDiv.classList.add('message', type);
                // Convert \n to <br> for proper formatting
                msgDiv.innerHTML = text.replace(/\n/g, '<br>');
                messagesContainer.appendChild(msgDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            sendBtn.addEventListener('click', () => sendMessage());
            inputField.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });

            // Make it globally available for the onclick handlers
            window.sendSuggestedMsg = sendMessage;
        });
    </script>

    <style>
        .typing { font-style: italic; opacity: 0.7; }
        .dot { animation: blink 1s infinite; margin-right: 2px; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink { 0% { opacity: 0.2; } 50% { opacity: 1; } 100% { opacity: 0.2; } }
    </style>
    @stack('scripts')
</body>
</html>
