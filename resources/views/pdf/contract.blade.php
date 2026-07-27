<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.5; color: #333; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        h1, h2, h3, h4 { margin: 0 0 10px 0; }
        .header-content { text-align: center; margin-bottom: 30px; font-weight: bold; }
        .footer { font-size: 11px; text-align: center; margin-top: 50px; }
        .page-break { page-break-after: always; }
        .article-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
        p { margin: 0 0 10px 0; text-align: justify; }
    </style>
</head>
<body>

    @php
        $companyName = $contract->client->company->company_name ?? null;
        $clientName = Str::upper($contract->client->first_name . ' ' . $contract->client->last_name);
        $startDate = $contract->start_date ? $contract->start_date->format('d/m/Y') : '.......';
        $endDate = $contract->end_date ? $contract->end_date->format('d/m/Y') : '.......';
    @endphp

    <!-- ATTESTATION DE DOMICILIATION -->
    <div class="header-content" style="font-size: 14px;">
        UNIVERSAL INVEST STRATEGY<br>
        <span style="font-weight: normal;">
        - Domiciliation Juridique<br>
        - Centre d’Affaires<br>
        - Conseil Juridique – Fiscal et Comptable<br>
        - Tenue de Comptabilité<br>
        - Diagnostic des entreprises<br>
        - Audit
        </span>
    </div>

    <h2 class="text-center font-bold mb-4" style="text-decoration: underline;">ATTESTATION DE DOMICILIATION</h2>

    <p>Nous soussignés, <strong>&lt;&lt;UNIVERSAL INVEST STRATEGY&gt;&gt;</strong> SARL AU, attestons par la présente que :</p>

    @if($companyName)
    <p>La société <strong>« {{ Str::upper($companyName) }} »</strong> SARL AU a domicilié son adresse fiscale dans nos locaux situés à :<br>
    @else
    <p>Monsieur/Madame <strong>{{ $clientName }}</strong> a domicilié son adresse fiscale dans nos locaux situés à :<br>
    @endif
    <strong>ANGLE RUE EL AARAR et BD LALLA ELYACOUT, IMM1, 3 ème ETAGE, APPT 8</strong> pour une période allant du<br>
    <strong>{{ $startDate }} au {{ $endDate }}</strong></p>

    <p>Nous déclarons en outre avoir pris connaissance qu’en application des dispositions de l’article 93 du CRCP, les rôles d’impôts, états de produits et autres titres de perception régulièrement émis sont exécutoires contre les redevables qui y sont inscrits, toutes personnes auprès desquelles les redevables ont élu domicile fiscal, avec leur accord.</p>

    <p>Les personnes auprès desquelles les redevables ont élu domicile fiscal avec accord, peuvent, de ce fait, faire l’objet d’action en recouvrement au même titre que les redevables à raison de la créance due au titre de l’activité concernée par la domiciliation.</p>

    <p>En foi de quoi, la présente attestation est délivrée pour lui permettre de procéder aux formalités administratives</p>

    <div class="text-right mb-4" style="margin-top: 40px; margin-right: 50px;">
        <p style="text-align: left; padding-left: 60%;">Fait à Casablanca<br>
        Le : <strong>{{ $contract->start_date ? $contract->start_date->format('d/m/Y') : now()->format('d/m/Y') }}</strong><br>
        <span class="font-bold">BACHRA YOUSSEF</span></p>
    </div>

    <div class="footer" style="margin-top: 80px;">
        Angle Rue Al AARAR et Av Lalla El Yacout 3 ème, imm1 Appartement 8<br>
        Tél:+212600800747<br>
        Email:contact@ui-strategy.com<br>
        RC : 496151 – patente : 34102034 – I.F : 50137892<br>
        – CNSS : 2507310 ICE:002752348000050
    </div>

    <div class="page-break"></div>

    <!-- CONTRAT DE DOMICILIATION -->
    <h2 class="text-center font-bold mb-4" style="text-decoration: underline;">CONTRAT DE DOMICILIATION</h2>

    <p>A/ Le cabinet <strong>« UNIVERSAL INVEST STRATEGY »</strong> SARL AU, représenté par son gérant -unique <strong>M.YOUSSEF BACHRA</strong> titulaire de la CIN N° BE604671 , ci-après dénommé «UNIVERSAL INVEST STRATEGY », d’une part, et d’autre part :</p>

    @if($companyName)
    <p>La société <strong>« {{ Str::upper($companyName) }} »</strong> SARL AU représenté par :<br>
    @else
    <p>
    @endif
    <strong>Mr/Mme {{ $clientName }}</strong> de nationalité Marocaine , né le {{ $contract->client->birth_date ? \Carbon\Carbon::parse($contract->client->birth_date)->format('d/m/Y') : '.......' }} , titulaire de CIN n° <strong>{{ $contract->client->cin }}</strong> ,Téléphone N°: {{ $contract->client->phone }}, adress Email:{{ $contract->client->email }}, demeurant à {{ $contract->client->address }}</p>

    <p>La présente domiciliation est établie dans le cadre de la loi marocaine, notamment les mesures engagées pour faciliter l’investissement de la création d’entreprise par les jeunes promoteurs. Elle est aussi régie par le code des obligations et contrats ainsi que par les documents annexes à la présente domiciliation.</p>

    <div class="article-title">ARTICLEII -OBJET</div>
    <p>Par le présent engagement de domiciliation, le cabinet UNIVERSAL INVEST STRATEGY SARL AU s’engage moyennent une rétribution Mensuelle, à mettre a la disposition du DOMICILIE qui accepte pour la durée et aux conditions fixées par la loi marocaine et par les conditions particulières et Générales, établies dans le présent engagement de domiciliation, un ensemble de prestation tel que défini si après:<br>
    La domiciliation de son entreprise (siège social et adresse commercial);<br>
    La réception, la tutelle et la mise à disposition du courrier reçu;<br>
    Réception des télécopies</p>

    <div class="article-title">ARTICLESIII -DUREE</div>
    <p>La présente domiciliation commence à courir <strong>Du {{ $startDate }} au {{ $endDate }}</strong> Elle sera résilié automatiquement sans préavis ou un écrit fait par UNIVERSAL INVEST STRATEGY<br>
    Et UNIVERSAL INVEST STRATEGY n'est plus responsable des préjudices générés par le client.<br>
    Elle sera renouvelée dans le cas où le client fait une demande écrite acceptée par UNIVERSAL INVEST STRATEGY</p>

    <div class="article-title">ARTICLEVI –OBLIGATION DU DOMICILIE</div>
    <p>Le DOMICILIE s’engage à régler, aux échéances de renouvellement, les redevances relatives au frais de domiciliation ainsi que tous les frais annexe facturés soit 165/mois<br>
    Le DOMICILIE s’engage à déclarer sans délai au domiciliataire selon les cas, soit tout changement relatif a son domicile personnel, soit s’il s’agit d’une personne morale, tout changement relatif a sa forme juridique, son objet, ainsi qu’au nom et au domici le personnel des personnes ayant le pouvoir générale de l’engager<br>
    En cas de non -respect des présentes , «UNIVERSAL INVEST STRATEGY» SARLAU pourra unilatéralement et à tout moment révoquer sans formalité ni indemnité le présent engagement de domiciliation, ses obligations seront alors suspendues sans contrepartie de plein droit et sa responsabilité dégagée<br>
    Le DOMICILIE s’oblige à remettre annuellement à la société domiciliataire les copies des reçus attestant du dépôt des différentes déclarations fiscales exigibles par la loi marocaine, notamment le bilan annuel et les déclarations de TVA.</p>

    <div class="article-title">ARTICLEV –RESILIATION DU CONTRAT</div>
    <p>Le présent contrat pourra être résilié de plein droit par le cabinet « «UNIVERSAL INVEST STRATEGY» SARLAU,30 jours après l’envoi au DOMICILIE d’une mise en demeure par lettre recommandée avec avis de réception, restée sans effet dans les cas suivant:<br>
    Non observation parle DOMICILIE de l’une quelconque des dispositions du présent engagement;<br>
    Non -paiement à leur échéance, des honoraires et/ou prestation de service;<br>
    Défaut de dépôt de la déclaration fiscale légale;<br>
    Défaut d’information du cabinet «UNIVERSAL INVEST STRATEGY» SARLAU d’un éventuel changement dans sa situation.</p>

    <div class="article-title">ARTICLEVI –ELECTION DE DOMICILE</div>
    <p>Pour l’exécution des présentes, les parties font élections de domicile chacune à son adresse portée sur le présent contrat et pour le DOMICILIE à son adresse personnelle ou à celle de son représentant légal. Tout changement d’adresse du DOMICILIE n’est opposable au cabinet « « UNIVERSAL INVEST STRATEGY» » SARL AU que s’il lui a été notifié par le DOMICILIE par lettre recommandée avec accusé de réception<br>
    UNIVERSAL INVEST STRATEGY informe les autorités comptantes l’administration des impôts, la trésorerie générale et de l’administration de la Douane, le cas échéant, dans un délai n’excédant pas 15 jours de la date de réception des plis recommandés par les services fiscaux qui n’auront pas été remis aux personnes domiciliées.<br>
    <em>N.B : Cette attestation de domiciliation est délivrée pour la création d’une nouvelle société et n’est pas valable pour un transfert de siège social</em></p>

    <div class="article-title">ARTICLE VII–FRAIS</div>
    <p>Les frais de légalisation sont Supportés par le domicilié.</p>

    <div style="margin-top: 40px; display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; text-align: left; padding-left: 20px;">
            <p class="font-bold">Mr. {{ $clientName }}</p>
        </div>
        <div style="display: table-cell; width: 50%; text-align: right; padding-right: 20px;">
            <p class="font-bold">Mr. YOUSSEF BACHRA</p>
        </div>
    </div>

</body>
</html>
