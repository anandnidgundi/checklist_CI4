<?php
 
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FileModel;
use App\Services\JwtService;
use App\Models\WaterTankCleaningModel;



class WaterTankCleaning extends ResourceController
{
  
    protected $waterTankCleaningModel;

    public function __construct()
    {
        $this->waterTankCleaningModel = new WaterTankCleaningModel();
    }

    public function createWaterTankCleaning() {}
}
