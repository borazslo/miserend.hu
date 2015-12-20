<?php

global $config;
include_once('load.php');

/**
 * @requires PHP 5.5.26
 */
class ApiTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providerTestApiSignup
     */
    public function testApiSignup($request, $json, $output) {
        $rawresponse = callPageFake('index.php', $request, $json);
        if (!$response = json_decode($rawresponse, true)) {
            echo "ERROR: " . $rawresponse . "\n";
        }
        $this->assertArraySubset($output, $response);
        
    }

    public function providerTestApiSignup() {
        return array(
            array(
                array('q'=>'api','action' => 'signup', 'v' => '3'),
                array('username' => 'vacskamati', 'password' => 'VanValami'),
                array('error' => 1)),
            array(
                array('q'=>'api','action' => 'signup', 'v' => '4'),
                array('username' => 'EgyHosszúÚjNév', 'email' => 'teszt@teszt.com', 'password' => 'sippala'),
                array('error' => 1)),
            array(
                array('q'=>'api','action' => 'signup', 'v' => '4'),
                array('username' => 'EgyHosszuUjNev', 'email' => 'teszt@teszt.com', 'password' => 'sippala'),
                array('error' => 0)),
            array(
                array('q'=>'api','action' => 'signup', 'v' => '4'),
                array('username' => 'EgyMasikHosszuUjNev', 'email' => 'teszt@teszt.com', 'password' => 'simoppo'),
                array('error' => 1)),
        );
    }

    /**
     * @dataProvider providerTestApiLogin     
     */
    public function testApiLogin($request, $json, $output) {
        $rawresponse = callPageFake('index.php', $request, $json);
        if (!$response = json_decode($rawresponse, true)) {
            echo "ERROR: " . $rawresponse . "\n";
        } elseif (isset($response['token'])) {
            $this->token = $response['token'];
        }
        $this->assertArraySubset($output, $response);
    }

    public function providerTestApiLogin() {
        return array(
            array(
                array('q' =>'api', 'action' => 'login', 'v' => '5'),
                array('username' => 'vacskamati', 'password' => 'VanValami'),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'login', 'v' => '3'),
                array('username' => 'vacskamati', 'password' => 'VanValami'),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'login', 'v' => '4'),
                array('username' => 'vacskamati', 'password' => 'vanvalami'),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'login', 'v' => '4'),
                array('username' => 'vacskamati', 'password' => 'VanValami'),
                array('error' => 0)),
        );
    }

    /**
     * @dataProvider providerTestApiUser
     */
    public function testApiUser($request, $json, $output) {
        $loginRequest = array('q'=>'api','action' => 'login', 'v' => '4');
        $loginPhpinput = array('username' => 'vacskamati', 'password' => 'VanValami');
        $loginOutput = array('error' => 0);
        $this->testApiLogin($loginRequest, $loginPhpinput, $loginOutput);

        $json['token'] = $this->token;
        
        $rawresponse = callPageFake('index.php', $request, $json);

        if (!$response = json_decode($rawresponse, true)) {
            echo "ERROR: " . $response . "\n";
        }
        $this->assertArraySubset($output, $response);
    }

    public function providerTestApiUser() {
        return array(
            array(
                array('q' =>'api', 'action' => 'user', 'v' => '3'),
                array(),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'user', 'v' => '4'),
                array(),
                array('error' => 0, 'user' => array('username' => 'vacskamati', 'nickname' => '', 'name' => 'Lázár Ervin', 'email' => 'egyik@gmail.com'))),
        );
    }

    /**
     * @dataProvider providerTestApiFavorites
     */
    public function testApiFavorites($request, $json, $output) {
        $loginRequest = array('q' =>'api', 'action'=>'login', 'v' => '4');
        $loginPhpinput = array('username' => 'vacskamati', 'password' => 'VanValami');
        $loginOutput = array('error' => 0);
        $this->testApiLogin($loginRequest, $loginPhpinput, $loginOutput);

        $json['token'] = $this->token;

        $rawresponse = callPageFake('index.php', $request, $json);

        if (!$response = json_decode($rawresponse, true)) {
            echo "ERROR: " . $rawresponse . "\n";
        }
        $this->assertArraySubset($output, $response);
    }

    public function providerTestApiFavorites() {
        return array(
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '3'),
                array(),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '4'),
                array(),
                array('error' => 0, 'favorites' => array())),
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '4'),
                array('add' => 13),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '4'),
                array('remove' => array(138, 139)),
                array('error' => 0, 'favorites' => array())),
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '4'),
                array('add' => 138),
                array('error' => 0, 'favorites' => array(138))),
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '4'),
                array('add' => array(138, 139)),
                array('error' => 0, 'favorites' => array(138, 139))),
            array(
                array('q' =>'api', 'action' => 'favorites', 'v' => '4'),
                array('add' => array(138, 139), 'remove' => 139),
                array('error' => 0, 'favorites' => array(138))),
        );
    }

    /**
     * @dataProvider providerTestApiReportByAnonym
     */
    public function testApiReportByAnonym($request, $json, $output) {
        $rawresponse = callPageFake('index.php', $request, $json);
        if (!$response = json_decode($rawresponse, true)) {
            echo "ERROR: " . $rawresponse . "\n";
        }
        $this->assertArraySubset($output, $response);
    }

    public function providerTestApiReportByAnonym() {
        global $config;
        $config['mail']['debug'] = 5;

        return array(
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '3'),
                array(),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '3'),
                array('tid' => 138, 'pid' => 0),
                array('error' => 0)),
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '3'),
                array('tid' => 138, 'pid' => 1),
                array('error' => 0)),
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '3'),
                array('tid' => 138, 'pid' => 2),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '3'),
                array('tid' => 138, 'pid' => 2, 'text' => 'Ez egy teszt megjegyzés. Elnézést.'),
                array('error' => 0)),
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '4'),
                array('tid' => 138, 'pid' => 2, 'text' => 'Ez egy teszt megjegyzés. Elnézést.'),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'report', 'v' => '4'),
                array('tid' => 138, 'pid' => 2, 'text' => 'Ez egy teszt megjegyzés. "dbdate" is van. Elnézést.', 'dbdate' => "-2 years"),
                array('error' => 0)),
        );
    }

    /**
     * @dataProvider providerTestApiUpdated
     */
    public function testApiUpdated($request, $output) {
        $response = callPageFake('index.php', $request);
        $this->assertEquals($output, $response);
    }

    public function providerTestApiUpdated() {
        return array(
            array(array('q' =>'api', 'action' => 'updated', 'v' => '3'), 0),
            array(array('q' =>'api', 'action' => 'updated', 'v' => '4'), 0),
            array(array('q' =>'api', 'action' => 'updated', 'v' => '3', 'datum' => date('Y-m-d')), 0),
            array(array('q' =>'api', 'action' => 'updated', 'v' => '4', 'datum' => date('Y-m-d')), 0),
            array(array('q' =>'api', 'action' => 'updated', 'v' => '3', 'datum' => '2011-11-11'), 1),
            array(array('q' =>'api', 'action' => 'updated', 'v' => '4', 'datum' => '2011-11-11'), 1),
        );
    }

    /**
     * @dataProvider providerTestApiTable     
     */
    public function testApiTable($request, $json, $output) {
        $responseRaw = callPageFake('index.php', $request, $json);
        $response = json_decode($responseRaw, true);
        if (is_array($response)) {
            $this->assertArraySubset($output, $response);
        } else {
            $this->assertEquals($output, $responseRaw);
        }
    }

    public function providerTestApiTable() {
        return array(
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '4', 'table' => 'miserend'),
                array(),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array(),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array('columns' => 'maci'),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array('columns' => array()),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array('columns' => array('id', 'nev', 'ismertnev', 'ismeretlennev')),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array('columns' => array('id', 'nev', 'ismertnev'), 'format' => 'valami'),
                array('error' => 1)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array('columns' => array('id', 'nev', 'ismertnev')),
                array('templomok' => array(array('id' => '138', 'nev' => 'Szent Anna templom', 'ismertnev' => 'Szabadhegyi templom',), array('id' => '139', 'nev' => 'Loyolai Szent Ignác-templom', 'ismertnev' => 'Bencés templom',)), 'error' => 0)),
            array(
                array('q' =>'api', 'action' => 'table', 'v' => '3', 'table' => 'templomok'),
                array('columns' => array('id', 'nev', 'ismertnev'), 'format' => 'text'),
                "id;nev;ismertnev;\n138;Szent Anna templom;Szabadhegyi templom;\n139;Loyolai Szent Ignác-templom;Bencés templom;\n"),
        );
    }

}
