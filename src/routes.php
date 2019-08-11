<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

define("DEBUG", false);

function logRequestIfDebug(Request $request, \Slim\Container $container, array $args) {
    /**
     * LOG FILE is located in <dummyserver>/logs/app.log
     */
    if (DEBUG) {
        $container->get('logger')->info($request->getMethod() . " " . $request->getUri());
        $queryParams = $request->getQueryParams();
        if (!empty($queryParams)) {
            $container->get('logger')->info("\t********** Query Params **********");
            foreach ($queryParams as $key => $value) {
                $container->get('logger')->info("\t$key = $value");
            }
        }
        $postParams = $request->getParsedBody();
        if (!empty($postParams)) {
            $container->get('logger')->info("\t********** POST Params **********");
            foreach ($postParams as $key => $value) {
                $container->get('logger')->info("\t$key = $value");
            }
        }
        if (!empty($args)) {
            $container->get('logger')->info("\t********** Path Params **********");
            foreach ($args as $key => $value) {
                $container->get('logger')->info("\t$key = $value");
            }
        }
        $files = $request->getUploadedFiles();
        if (!empty($files)) {
            $container->get('logger')->info("\t********** Uploaded files **********");
            foreach ($files as $key => $value) {
                $container->get('logger')->info("\t$key: {$value->getClientFilename()} ({$value->getClientMediaType()})");
            }
        }
    }
}

function isAuthorized(Request $request, Response $response, array $args) {
    $isAuthorized = true;

    //TODO: CHECK IF AUTHORIZATION IS PRESENT

    return $isAuthorized;
}

function createResponseForUnauthorizedAccess(Response $response) {
    return $response->withStatus(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED);
}

return function (App $app) {
    $container = $app->getContainer();
    $logger = $container->get('logger');


    $app->get('/graders', function (Request $request, Response $response, array $args) use ($container) {

        logRequestIfDebug($request, $container, $args);

        if (!isAuthorized($request, $response, $args)) {
            return createResponseForUnauthorizedAccess($response);
        }

        $graders = array("graders" => array(
                "Dummy Grader 1" => "id_dummygrader_1"
                , "Dummy Grader 2" => "id_dummygrader_2"
                , "Dummy Grader 3" => "id_dummygrader_3"
                , "Dummy Grader 4" => "id_dummygrader_4"
        ));
        return $response->write(json_encode($graders));
    });

    $app->get('/{graderid}/gradeprocesses/{gradeprocessid}', function (Request $request, Response $response, array $args) use ($container) {

        logRequestIfDebug($request, $container, $args);

        if (!isAuthorized($request, $response, $args)) {
            return createResponseForUnauthorizedAccess($response);
        }

        $isGradingFinished = true;
        if ($isGradingFinished) {

            $file = "../files_to_serve/response_right.zip";
            $fh = fopen($file, 'rb');
            $stream = new \Slim\Http\Stream($fh);

            return $response->withHeader('Content-Type', 'application/force-download')
                            ->withHeader('Content-Type', 'application/octet-stream')
                            ->withHeader('Content-Type', 'application/download')
                            ->withHeader('Content-Description', 'File Transfer')
                            ->withHeader('Content-Transfer-Encoding', 'binary')
                            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
                            ->withHeader('Expires', '0')
                            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                            ->withHeader('Pragma', 'public')
                            ->withBody($stream)
                            ->withStatus(\Slim\Http\StatusCode::HTTP_OK);
        } else {
            return $response->withStatus(\Slim\Http\StatusCode::HTTP_ACCEPTED);
        }
    });

    $app->map(['HEAD'], '/tasks/{taskuuid}', function (Request $request, Response $response, array $args) use ($container) {

        logRequestIfDebug($request, $container, $args);

        if (!isAuthorized($request, $response, $args)) {
            return createResponseForUnauthorizedAccess($response);
        }

        $doesTaskExist = false;

        if (!$doesTaskExist) {
            $response = $response->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND);
        }

        return $response;
    });

    $app->post('/{graderid}/gradeprocesses', function (Request $request, Response $response, array $args) use ($container) {

        logRequestIfDebug($request, $container, $args);

        if (!isAuthorized($request, $response, $args)) {
            return createResponseForUnauthorizedAccess($response);
        }

        $queryParams = $request->getQueryParams();
        $requestFiles = $request->getUploadedFiles();

        if (!isset($requestFiles['submission'])) {
            return $response->withStatus(\Slim\Http\StatusCode::HTTP_BAD_REQUEST);
        }
        if (!isset($queryParams['graderid'])) {
            return $response->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND);
        }

        /*
          //Copy submission file to dummyserver in case it should be inspected
          //Needs write permissions for webserver on folder uploaded_files in dummy-server root directory
          //Commented out by default to prevent hard disk spam
          $submissionfile = $requestFiles['submission'];
          $time = microtime();
          $submissionfile->moveTo("../uploaded_files/submission_{$time}_.zip");
         */
        $response = $response->withJson(["gradeProcessId" => uniqid()]);

        return $response->withStatus(\Slim\Http\StatusCode::HTTP_CREATED);
    });
};
