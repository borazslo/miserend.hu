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
		printr($user);
	    if($output == array())
	    	$this->assertEquals($output,(array) $user);
	    else
	    	$this->assertArraySubset($output,(array) $user);
	}

    public function providerTestUserUser()
	{
		$guest = array('loggedin'=>false,'uid'=>0,'username'=>'*vendeg*','nickname'=>'*vendég*');
		$sampleuser = array('uid'=>5258,'username'=>'verem','nickname'=>'','email'=>'egyik@gmail.com');
		return array(
			array(0,$guest),
			array('',$guest),
			array(5258,$sampleuser),
			array('verem',$sampleuser),
			array('sdf4',$guest),
			array(123123233,array()),
		);
	}
}

?>