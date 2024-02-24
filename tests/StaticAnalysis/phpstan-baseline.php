<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$churchId of method App\\\\Components\\\\ApiClients\\\\CommunitiesApiClient\\:\\:getCommunityInfoWithChurchId\\(\\) expects int, int\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Components/ApiClients/CommunitiesApiClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getAddress\\(\\) on App\\\\Components\\\\ApiClients\\\\Response\\\\CommunitiesResponse\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Components/ApiClients/Tests/CommunitiesApiClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getCity\\(\\) on App\\\\Components\\\\ApiClients\\\\Response\\\\CommunitiesResponse\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Components/ApiClients/Tests/CommunitiesApiClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getCommunities\\(\\) on App\\\\Components\\\\ApiClients\\\\Response\\\\CommunitiesResponse\\|null\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Components/ApiClients/Tests/CommunitiesApiClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getInstituteName\\(\\) on App\\\\Components\\\\ApiClients\\\\Response\\\\CommunitiesResponse\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Components/ApiClients/Tests/CommunitiesApiClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getInstituteUrl\\(\\) on App\\\\Components\\\\ApiClients\\\\Response\\\\CommunitiesResponse\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Components/ApiClients/Tests/CommunitiesApiClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$body of class Symfony\\\\Component\\\\HttpClient\\\\Response\\\\MockResponse constructor expects iterable\\<string\\|Throwable\\>\\|string, string\\|false given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Components/ApiClients/Tests/CommunitiesApiClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$addresses of method Symfony\\\\Component\\\\Mime\\\\Email\\:\\:to\\(\\) expects string\\|Symfony\\\\Component\\\\Mime\\\\Address, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Controller/UserController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method removeElement\\(\\) on Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\User\\>\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/Church.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method toArray\\(\\) on Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\User\\>\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/Church.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Entity\\\\Church\\:\\:\\$usersWhoFavored \\(Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\User\\>\\|null\\) does not accept array\\{App\\\\Entity\\\\User\\}\\|Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\User\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/Church.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method contains\\(\\) on Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\Church\\>\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method removeElement\\(\\) on Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\Church\\>\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method toArray\\(\\) on Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\Church\\>\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Entity\\\\User\\:\\:getUserIdentifier\\(\\) should return string but returns string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Entity\\\\User\\:\\:\\$favorites \\(Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\Church\\>\\|null\\) does not accept array\\{App\\\\Entity\\\\Church\\}\\|Doctrine\\\\Common\\\\Collections\\\\Collection\\<int, App\\\\Entity\\\\Church\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Entity/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getId\\(\\) on App\\\\Entity\\\\User\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Form/Types/UserType.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getRoles\\(\\) on App\\\\Entity\\\\User\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Form/Types/UserType.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$string of method Symfony\\\\Component\\\\String\\\\Slugger\\\\SluggerInterface\\:\\:slug\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Repository/ChurchRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$key of method Symfony\\\\Component\\\\HttpFoundation\\\\ParameterBag\\:\\:getInt\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Request/QueryParameterEntityResolver.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$key of method Symfony\\\\Component\\\\HttpFoundation\\\\ParameterBag\\:\\:has\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Request/QueryParameterEntityResolver.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getContainer\\(\\) on Symfony\\\\Component\\\\HttpKernel\\\\KernelInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/LiturgicalDayControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$body of class Symfony\\\\Component\\\\HttpClient\\\\Response\\\\MockResponse constructor expects iterable\\<string\\|Throwable\\>\\|string, string\\|false given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/LiturgicalDayControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$actualString of method PHPUnit\\\\Framework\\\\Assert\\:\\:assertStringEqualsFile\\(\\) expects string, string\\|false given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/LiturgicalDayControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getCollector\\(\\) on Symfony\\\\Component\\\\HttpKernel\\\\Profiler\\\\Profile\\|false\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/UserControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Tests\\\\ApplicationTests\\\\Controller\\\\UserControllerTest\\:\\:getValidRegistrationFormData\\(\\) should return array\\<string, string\\> but returns array\\<string, string\\|true\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/UserControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$email of method Symfony\\\\Bundle\\\\FrameworkBundle\\\\Test\\\\KernelTestCase\\:\\:assertEmailHtmlBodyContains\\(\\) expects Symfony\\\\Component\\\\Mime\\\\RawMessage, Symfony\\\\Component\\\\Mime\\\\RawMessage\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/UserControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$email of method Symfony\\\\Bundle\\\\FrameworkBundle\\\\Test\\\\KernelTestCase\\:\\:assertEmailSubjectContains\\(\\) expects Symfony\\\\Component\\\\Mime\\\\RawMessage, Symfony\\\\Component\\\\Mime\\\\RawMessage\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/UserControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$text of method Symfony\\\\Bundle\\\\FrameworkBundle\\\\Test\\\\KernelTestCase\\:\\:assertEmailHtmlBodyContains\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../ApplicationTests/Controller/UserControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getContainer\\(\\) on Symfony\\\\Component\\\\HttpKernel\\\\KernelInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../DatabaseMigrationTests/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getId\\(\\) on App\\\\Entity\\\\User\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../DatabaseMigrationTests/UserTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
