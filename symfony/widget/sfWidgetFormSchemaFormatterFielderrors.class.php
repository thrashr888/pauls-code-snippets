<?php

/**
 * sfWidgetFormSchemaFormatterFielderrors.class.php
 * symfony 1.2 class
 *
 * @author thrashr888
 * @name sfWidgetFormSchemaFormatterFielderrors
 *
 * @example
 *
 * In Action:
 * $this->form = new ContactsForm();
 * $this->form->setFormatter('fielderrors');
 *
 * In Template:
 * echo $form
 *
 */
class sfWidgetFormSchemaFormatterFielderrors extends sfWidgetFormSchemaFormatter {
	protected
		$errors = null,

		$rowFormat = "<p class=\"field\">%label%<br />\n%hidden_fields% %field% %help%</p>\n",
		$helpFormat = "<br /><span class=\"help\">%help%</span>\n",
		$decoratorFormat = "%content%",

		$errorRowFormat            = '',
		$errorListFormatInARow     = "  <ul class=\"error_list\">\n%errors%</ul>\n",
		$errorRowFormatInARow      = "    <li>%error%</li>\n",
		$namedErrorRowFormatInARow = "    <li>%name%: %error%</li>\n";


	public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
	{
		if(!is_null($errors) && count($errors)){
			$label = str_replace("class=\"", "class=\"error ", $label);
		}

		return parent::formatRow($label, $field, $errors, $help, $hiddenFields);
	}

	public function generateLabel($name, $attributes = array())
	{
		$attributes['class'] = " ";
		return parent::generateLabel($name, $attributes);
	}
}// decorator class
