<?php
namespace Vivo\Controller;

use Vivo\Http\StreamResponse;

use Vivo\CMS\ComponentFactory;
use Vivo\CMS\CMS;
use Vivo\CMS\Model\Site;
use Vivo\Controller\Exception;
use Vivo\IO\InputStreamInterface;
use Vivo\UI\Component;
use Vivo\UI\Exception\ExceptionInterface as UIException;
use Vivo\UI\TreeUtil;

use Zend\EventManager\EventInterface as Event;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ModelInterface;

/**
 * The front controller which is responsible for dispatching all requests for documents and files in CMS repository.
 */
class CMSFrontController implements DispatchableInterface,
    InjectApplicationEventInterface
{

    /**
     * @var \Zend\Mvc\MvcEvent
     */
    protected $event;

    /**
     * @var \Vivo\CMS\CMS
     */
    private $cms;

    /**
     * @var \Vivo\Model\Site
     */
    private $site;

    /**
     * @var \Vivo\CMS\ComponentFactory
     */
    private $componentFactory;

    /**
     * @var \Vivo\UI\TreeUtil
     */
    private $treeUtil;

    /**
     * @param ComponentFactory $componentFactory
     */
    public function setComponentFactory(ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param CMS $cms
     */
    public function setCMS(CMS $cms)
    {
        $this->cms = $cms;
    }

    /**
     * @param Site $site
     */
    public function setSite(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Dispatch CMS request
     * @param Request $request
     * @param Response $response
     * @todo should we render UI in controller dispatch action?
     */
    public function dispatch(Request $request, Response $response = null)
    {
        //TODO: add exception when document doesn't exist
        //TODO: redirects based on document properties(https, $document->url etc.)
        $response->getHeaders()->addHeaderLine('X-Generated-By: Vivo')
            ->addHeaderLine(
                'X-Generated-At: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

        $documentPath = $this->event->getRouteMatch()->getParam('path');
        $document = $this->cms->getDocument($documentPath, $this->site);
        $root = $this->componentFactory->getRootComponent($document);

        $this->treeUtil->setRoot($root);
        $root->init(); //TODO lazy init

        if ($this->getRequest()->isXmlHttpRequest()) {
            //if request is  ajax call, we use result of method
            $result = $this->handleAction();
        } else {
            $this->handleAction();
            $result = $root->view();
        }

        $root->done();

        if ($result instanceof ModelInterface) {
            $this->event->setViewModel($result);
        } elseif ($result instanceof InputStreamInterface) {
            //skip rendering phase
            $response->setStream($result);
            return $response;
        } elseif (is_string($result)) {
            //skip rendering phase
            $response->setContent($result);
            return $response;
        }
    }

    /**
     * Handles action on component.
     */
    public function handleAction()
    {
        //TODO is a better way how to obtain params?
        //TODO create router for asembling and matching path of action
        $request = $this->getRequest();
        if (!$action = $request->getQuery('act')) {
            if (!$action = $request->getPost('act')) {
                return;
            } else {
                $params = $request->getPost('args', array());
            }
        } else {
            $params = $request->getQuery('args', array());
        }

        $parts = explode(Component::COMPONENT_SEPARATOR, $action);
        $action = array_pop($parts);
        $path = implode(Component::COMPONENT_SEPARATOR, $parts);
        return $this->treeUtil->invokeAction($path, $action, $params);
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return \Zend\Mvc\MvcEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param TreeUtil $treeUtil
     */
    public function setTreeUtil(TreeUtil $treeUtil)
    {
        $this->treeUtil = $treeUtil;
    }

    /**
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest() {
        return $this->event->getRequest();
    }
}
