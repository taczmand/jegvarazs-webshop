<?php

return [
    'agent_key' => env('SZAMLAZZHU_AGENT_KEY'),
    'seller_name' => env('SZAMLAZZHU_SELLER_NAME') ?? '',
    'seller_tax_number' => env('SZAMLAZZHU_SELLER_TAX_NUMBER') ?? '',
    'download_pdf' => env('SZAMLAZZHU_DOWNLOAD_PDF', true),
];
