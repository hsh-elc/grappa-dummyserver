<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

define("DEBUG", false);

function logRequestIfDebug(Request $request, \Slim\Container $container) {
    if (DEBUG) {
        $container->get('logger')->info("##########################");
        $container->get('logger')->info($request->getMethod());
        $container->get('logger')->info($request->getUri());
        $container->get('logger')->info("Request Params:");
        foreach ($request->getQueryParams() as $key => $value) {
            $container->get('logger')->info("\t$key = $value");
        }
        $container->get('logger')->info("##########################");
    }
}

function checkAuthorization(Request $request, Response $response, array $args) {
    $returnUnauthorized = false;

    //TODO: CHECK IF AUTHORIZATION IS PRESENT

    if ($returnUnauthorized) {
        $response = $response->withStatus(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED);
    }
    return $response;
}

return function (App $app) {
    $container = $app->getContainer();
    $logger = $container->get('logger');


    $app->get('/graders', function (Request $request, Response $response, array $args) use ($container, $logger) {

        $response = checkAuthorization($request, $response, $args);

        logRequestIfDebug($request, $container);

        $graders = array("graders" => array(
                "Dummy Grader 1" => "id_dummygrader_1"
                , "Dummy Grader 2" => "id_dummygrader_2"
                , "Dummy Grader 3" => "id_dummygrader_3"
                , "Dummy Grader 4" => "id_dummygrader_4"
        ));
        return $response->write(json_encode($graders));
    });

    $app->map(['HEAD'], '/tasks/[{taskuuid}]', function (Request $request, Response $response, array $args) use ($container, $logger) {

        $response = checkAuthorization($request, $response, $args);

        $doesTaskExist = false;

        if (!$doesTaskExist) {
            $response = $response->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND);
        }

        return $response;
    });
};
