<?php

namespace App\Errors;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Validator\ConstraintViolation;

class ErrorFormatter
{
    private Form $form;
    private FormErrorIterator $errors;
    private array $sortedErrors = ['origin' => 'form'];

    public function __construct(Form $form)
    {
        $this->form     = $form;
        $this->errors   = $form->getErrors(true);
    }

    public function sortErrors(): array
    {
        foreach ($this->errors as $error) {
            $cause  = $error->getCause();
            $key    = $this->form->getName();

            if ($cause instanceof ConstraintViolation) {

                if (preg_match('(data|children)', $cause->getPropertyPath()) === 1) {
                    $path           = array_diff(explode('.', $cause->getPropertyPath()), ['data', 'children']);
                    $originName     = $error->getOrigin()->getName();

                    foreach ($path as $index => $subPath) {
                        $key .= '_' . str_replace(['children', '[', ']'], '', $subPath);
                    }
                    $key .= '_' . $originName;
                }
                else
                    $key = null;
            }
            if ($key)
                $this->sortedErrors[$key] = $error->getMessage();
        }

        return $this->sortedErrors;
    }
}
