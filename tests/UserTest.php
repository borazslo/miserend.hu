<?php

class UserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providerTestUserUser
     */
    public function testUserUser($input, $output) {
        $user = new User($input);
        if ($output == array()) {
            $this->assertEquals($output, (array) $user);
        } else
            $this->assertArraySubset($output, (array) $user);
    }

    public function providerTestUserUser() {
        $guest = array('loggedin' => false, 'uid' => 0, 'username' => '*vendeg*', 'nickname' => '*vendég*');
        $sampleuser = array('uid' => 10, 'username' => 'vacskamati', 'nickname' => '', 'email' => 'egyik@gmail.com');
        return array(
            array(0, $guest),
            array('', $guest),
            array(10, $sampleuser),
            array('vacskamati', $sampleuser),
            array('sdf4', $guest),
            array(12313233, array()),
        );
    }

    /**
     * @requires PHP 5.5.26
     */
    public function testUserDelete() {
        $user = new \User('EgyHosszuUjNev');
        $success = $user->delete();
        $this->assertTrue($success);
    }

}
