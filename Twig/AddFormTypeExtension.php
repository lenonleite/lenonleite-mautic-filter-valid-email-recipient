<?php

declare(strict_types=1);

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Twig;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Symfony\Component\HttpFoundation\Request;
use Mautic\LeadBundle\Model\ListModel;

class AddFormTypeExtension extends AbstractExtension
{
    public function __construct(
        protected FormFactoryInterface $formFactory,
        private RequestStack $requestStack,
        private ListModel $leadList
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('customForm', $this->getFormRow(...)),
        ];
    }

    public function getFormRow($formType,$key,$var='form')
    {
        $class = new $formType($this->formFactory, $this->requestStack, $this->leadList);
        $form = $this->formFactory->create($class::class)->createView();
//        dump($form->vars);
        return $form->vars['form']->children[$key];
    }
}
