<?php
namespace {{namespace}}\Core\Traits;

trait HasModel
{
    // Method to retrieve or load the model based on model_name
    public function getModel()
    {
        $model = null;
        if (isset($this->model_name)) {
            // Load the model if it's not already loaded
            $model = ee('Model')->get($this->model_name);
        }
        return $model;
    }
    public function makeModel() {
      $model = null;
      if (isset($this->model_name)) {
          // Load the model if it's not already loaded
          $model = ee('Model')->make($this->model_name);
      }
      return $model;
    }

}
