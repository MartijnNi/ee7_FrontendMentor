<?php
namespace {{namespace}}\Core\Controllers;

use {{namespace}}\Core\Traits\HandlesForms;
use {{namespace}}\Core\Traits\HasForms;
use {{namespace}}\Core\Base\Controller;
use {{namespace}}\Core\Traits\HasModel;
use {{namespace}}\Core\Traits\HasTags;

class TagController extends Controller
{
    use HasModel;
    use HasTags;
    use HasForms;
    use HandlesForms;

    protected $model_name = '{{addonName}}:Settings';

    public function create() {
        $i = 0;
        $tag = $this->tag();

        // Create the form
        // ----------------------------------------------------------------
        $form = $this->makeForm($tag, $this->helpers->action('Post'));

        // Set Form variables
        // ----------------------------------------------------------------
        $this->addFormVariables($i, $tag);

        if ($tag->getTagParam('output') == "n") {
            $tag->setVariables($i,$this->helpers->variables($form));
        } else {
            $tag->tagdata = $form;
        }

        return $tag->parse();
    }

    public function update(){
        if ($this->request->isAjax()) {
            return $this->response->success([], 'It just works!');
        } 

        $returnURL = $this->request->input('return_url') ? $this->request->input('return_url') : ee()->functions->form_backtrack('-2');
        return ee()->functions->redirect($returnURL);
        
    }  
}