<?php

return [
    /**
     * Folosește procesare asincronă pentru generarea documentelor IDOC
     * true = folosește cozi pentru procesare asincronă (recomandat pentru producție)
     * false = procesare sincronă (recomandat pentru dezvoltare)
     */
    'use_queue' => env('IDOC_USE_QUEUE', true),

    /**
     * Directoarele pentru stocarea fișierelor generate
     */
    'directories' => [
        'idoc_client' => 'IDOC_client',
        'idoc_furnizor' => 'IDOC_furnizor',
        'facturi' => 'documente_site/facturi',
        'avize' => 'documente_site/avize',
        'comenzi' => 'documente_site/comenzi',
    ],

    /**
     * Șabloanele pentru diferite tipuri de documente
     */
    'templates' => [
        'factura' => 'documente_site/facturi/factura_exemplu.txt',
        'aviz' => 'documente_site/avize/aviz_livrare_exemplu.txt',
        'confirmare' => 'documente_site/comenzi/confirmare_comanda_exemplu.txt',
    ],

    /**
     * Formatul IDOC SAP
     */
    'sap_format' => [
        'order_message_type' => 'ORDERS',
        'delivery_message_type' => 'DESADV',
        'invoice_message_type' => 'INVOIC',
        'segment_prefix' => 'E1',
        'header_segment' => 'EDK01',
        'partner_segment' => 'EDKA1',
        'item_segment' => 'EDP01',
        'summary_segment' => 'EDS01',
        'control_record_type' => 'EDI_DC40',
    ],

    /**
     * Setări pentru logging
     */
    'logging' => [
        'enabled' => true,
        'channel' => 'idoc',
    ],
]; 