<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPhone validates phone numbers.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Paul Thrasher <paul@dogster.com>
 * @version    SVN: $Id: sfValidatorPhone.class.php$
 */
class dValidatorPhone extends sfValidatorRegex
{
  /**
   * @see sfValidatorRegex
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $pattern = "^(1\s*[-\/\.]?)?(\((\d{3})\)|(\d{3}))\s*[-\/\.]?\s*(\d{3})\s*[-\/\.]?\s*(\d{4})\s*(([xX]|[eE][xX][tT])\.?\s*(\d+))*$";
    $this->setOption('pattern', "/$pattern/i");
  }
}
