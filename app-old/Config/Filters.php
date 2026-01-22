<?php 

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use App\Filters\Cors;
use App\Filters\SecurityHeaders;  // ✅ Add this line
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
    public $aliases = [
        'cors'          => Cors::class,
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'authFilter'    => \App\Filters\AuthFilter::class,
        'securityheaders' => SecurityHeaders::class,  // ✅ Register SecurityHeaders filter
    ];

    public $globals = [
        'before' => [
            'cors',
            // 'honeypot',
            // 'csrf',
        ],
        'after'  => [
            'cors',
            'toolbar',
            'securityheaders',  // ✅ Use the new filter instead of an anonymous function
            'csrf',
        ],
    ];

    public $methods = [];

    public $filters = [
        'cors' => [
            'before' => ['api/*'],
            'after'  => ['api/*'],
        ],
    ];
}
