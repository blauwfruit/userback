<?php
/**
 *   Bfuserback
 *
 *   Do not copy, modify or distribute this document in any form.
 *
 *   @author     Vitaliy <vitaly@blauwfruit.nl>
 *   @copyright  Copyright (c) 2013-2023 blauwfruit (http://blauwfruit.nl)
 *   @license    Proprietary Software
 *
 */

class UserbackAccessModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        $this->context->cookie->{$this->module->name} = true;
        $this->context->cookie->write();

        Tools::redirect('index');
    }
}
