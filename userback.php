<?php
/**
 *   Userback
 *
 *   Do not copy, modify or distribute this document in any form.
 *
 *   @author     Vitaliy <vitaly@blauwfruit.nl>
 *   @copyright  Copyright (c) 2013-2023 blauwfruit (http://blauwfruit.nl)
 *   @license    Proprietary Software
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Userback extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'userback';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'blauwfruit';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('UserBack');
        $this->description = $this->l('Adds a UserBack feedback button, by visiting to /feedback');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        Configuration::deleteByName('USERBACK_ACCESS_TOKEN');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitUserbackModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign(array(
            'accessUrl' => $this->context->shop->getBaseURL()  . 'feedback',
        ));

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $this->renderForm() . $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUserbackModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-tools',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('UserBack access token'),
                        'name' => 'USERBACK_ACCESS_TOKEN',
                        'label' => $this->l('Access token'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'USERBACK_ACCESS_TOKEN' => Configuration::get('USERBACK_ACCESS_TOKEN'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayHeader()
    {
        $userBackToken = Configuration::get('USERBACK_ACCESS_TOKEN');

        if (!$userBackToken) {
            return;
        }

        if (!$this->context->cookie->{$this->name}) {
            return;
        }

        $this->context->smarty->assign(array(
            'userBackToken' => $userBackToken,
        ));

        return $this->context->smarty->fetch($this->local_path.'views/templates/hook/display_header.tpl');
    }

    public function hookModuleRoutes($params)
    {
        return array(
            'module-' . $this->name . 'Access' => array(
                'controller' => 'access',
                'rule' =>  'feedback',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name
                )
            ),
        );
    }
}
