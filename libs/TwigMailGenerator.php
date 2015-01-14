<?php

class TwigMailGenerator
{
    protected $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getMessage($identifier, $parameters = array())
    {
        $template = $this->twig->loadTemplate($identifier.'.twig'); // Define your own schema

        $subject  = $template->renderBlock('subject',   $parameters);
        $bodyHtml = $template->renderBlock('body_html', $parameters);
        $bodyText = $template->renderBlock('body_text', $parameters);
        return array("subject" => $subject,"bodyHtml" => $bodyHtml, "bodyText" => $bodyText);
    }
}
?>