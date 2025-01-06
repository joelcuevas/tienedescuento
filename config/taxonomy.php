<?php 

return  [
    'mx' => [
        'Electrónica' => [
            'keywords' => ['electronica', 'electronico', 'tecnologia', 'computadoras-y-tablets'],
            'children' => [
                'Celulares' => [
                    'keywords' => ['celulares', 'reacondicionados', 'iphone'],
                ],
                'Tablets' => [
                    'keywords' => ['tablets', 'ipad'],
                ],
                'Laptops' => [
                    'keywords' => ['laptop'],
                ],
                'Pantallas' => [
                    'keywords' => ['pantallas', 'pantallas-y-proyectores'],
                ],
            ],
        ],

        'Línea Blanca' => [
            'keywords' => ['linea-blanca', 'linea-blanca-y-electrodomesticos'],
            'children' => [
                'Refrigeradores' => [
                    'keywords' => ['refrigeradores', 'refrigeradores-y-congeladores'],
                ],
                'Lavadoras y Secadoras' => [
                    'keywords' => ['lavadoras', 'secadoras', 'lavasecadora', 'lavasecadoras', 'centro-de-lavado', 'centros-de-lavado', 'lavadoras-y-secadoras'],
                ],
            ],
        ],

        'Videojuegos' => [
            'keywords' => ['videojuegos'],
            'children' => [
                'Nintendo' => [
                    'keywords' => ['nintendo', 'switch', 'nintendo-switch'],
                ],
                'PlayStation' => [
                    'keywords' => ['playstation'],
                ],
                'Xbox' => [
                    'keywords' => ['xbox'],
                ],
                'Accesorios' => [
                    'keywords' => ['accesorios', 'accesorios-para-videojuegos', 'accesorios-xbox', 'accesorios-playstation', 'accesorios-nintendo'],
                ],
                'Gaming' => [
                    'keywords' => ['gaming', '!computadoras-para-gamers'],
                ],
            ],
        ],
    ],
];