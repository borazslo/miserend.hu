<?php
global $config;
include 'load.php';


class UserTest extends \PHPUnit_Framework_TestCase
{
	/**
 	* @dataProvider providerTestUserUser
 	*/
	public function testUserUser($input,$output)
	{
		$user = new User($input);
	    if($output == array())
	    	$this->assertEquals($output,(array) $user);
	    else
	    	$this->assertArraySubset($output,(array) $user);
	}

    public function providerTestUserUser()
	{
		return array(
			array(0,array('loggedin'=>false,'uid'=>0,'username'=>'*vendeg*','nickname'=>'*vendég*')),
			array('',array('loggedin'=>false,'uid'=>0,'username'=>'*vendeg*','nickname'=>'*vendég*')),
			array(5258,array('uid'=>5258,'username'=>'verem','nickname'=>'')),
			array('verem',array('uid'=>5508,'username'=>'verem','nickname'=>'')),
			array('sdf4',array('loggedin'=>false,'uid'=>0,'username'=>'*vendeg*','nickname'=>'*vendég*')),
			array(123123233,array()),
		);
	}
}

?>