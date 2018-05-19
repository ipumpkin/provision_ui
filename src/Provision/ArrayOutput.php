<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drupal\provision_ui\Provision;

use Symfony\Component\Console\Output\Output;

class ArrayOutput extends Output
{
    private $buffer = [];
    
    private $tmp = '';

    /**
     * Empties buffer and returns its content.
     *
     * @return string
     */
    public function fetch()
    {
        $content = $this->buffer;
        $content[] = $this->tmp;
        
        $this->buffer = [];
        $this->tmp = '';

        
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
      if ($newline) {
          $this->buffer[] = $this->tmp;
          $this->tmp = $message;
        }else{
          $this->tmp .= $message;
        }
    }
}
