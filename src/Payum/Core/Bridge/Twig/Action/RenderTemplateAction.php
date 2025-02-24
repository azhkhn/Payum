<?php

namespace Payum\Core\Bridge\Twig\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Twig\Environment;

class RenderTemplateAction implements ActionInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @param string      $layout
     */
    public function __construct(Environment $twig, $layout)
    {
        $this->twig = $twig;
        $this->layout = $layout;
    }

    public function execute($request)
    {
        /** @var RenderTemplate $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setResult($this->twig->render($request->getTemplateName(), array_replace(
            [
                'layout' => $this->layout,
            ],
            $request->getParameters()
        )));
    }

    public function supports($request)
    {
        return $request instanceof RenderTemplate;
    }
}
