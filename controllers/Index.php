<?php

namespace GromIT\RoutesBrowser\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use GromIT\RoutesBrowser\Actions\FindRoute;
use GromIT\RoutesBrowser\Actions\GetRouteDetails;
use GromIT\RoutesBrowser\Actions\ListRoutes;

class Index extends Controller
{
    protected $requiredPermissions = ['gromit.routesbrowser.view'];

    /**
     * @var \GromIT\RoutesBrowser\Actions\ListRoutes
     */
    private $getRoutes;

    /**
     * @var \GromIT\RoutesBrowser\Actions\FindRoute
     */
    private $findRoute;

    /**
     * @var \GromIT\RoutesBrowser\Actions\GetRouteDetails
     */
    private $getRouteDetails;

    public function __construct(
        ListRoutes $getRoutes,
        FindRoute $findRoute,
        GetRouteDetails $getRouteDetails
    )
    {
        parent::__construct();

        BackendMenu::setContext('GromIT.RoutesBrowser', 'routes');

        $this->getRoutes       = $getRoutes;
        $this->findRoute       = $findRoute;
        $this->getRouteDetails = $getRouteDetails;
    }

    /**
     * @throws \ReflectionException
     */
    public function index(): void
    {
        $this->pageTitle = 'HTTP Routes';

        $this->bodyClass = 'compact-container';

        $this->addCss('/plugins/gromit/routesbrowser/controllers/index/assets/index.css');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/axios.min.js');
        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js');

        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/list-filter.js');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/http-codes.js');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/open-route.js');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/copy-links.js');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/collapse-list.js');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/index.js');
        $this->addJs('/plugins/gromit/routesbrowser/controllers/index/assets/scripts/utils.js');

        $this->vars['routes'] = $this->getRoutes->execute();

        if (!request()->ajax() && get('method') && get('uri')) {
            $this->prepareVars(strtoupper(get('method')), urldecode(post('uri')));
        } else {
            $this->vars['details'] = null;
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function onShowDetails(): void
    {
        $method = post('method');
        $uri    = urldecode(post('uri'));

        $this->prepareVars($method, $uri);
    }

    /**
     * @throws \ReflectionException
     */
    private function prepareVars(string $method, string $uri): void
    {
        $route   = $this->findRoute->execute($method, $uri);
        $details = $route ? $this->getRouteDetails->execute($route, $method) : null;

        $this->vars['details'] = $details;

        if (!request()->ajax()) {
            $this->vars['headers'] = get('headers', []);
        } else {
            $this->vars['headers'] = [];
        }
    }
}
