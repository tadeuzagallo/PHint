<?php require('./PHint.php');

class Person extends TZObject {
	protected $definition = array(
		'name' => TZString,
		'age' => TZInteger,
		'childs' => TZArray,
		'spouse' => 'Person',
		'sex' => TZInteger,
	);

	const MALE = 1;
	const FEMALE = 2;

	public function TZVoid_getMarried(Person $person) {
		$this->spouse = $person;
		$person->spouse = $this;
	}

	public function TZString_talk() {
		return "Hello, my name is {$this->name}!";
	}

}

$john = new Person();
$jane = new Person();

$john->name = "John";
$jane->name = "Jane";

$john->age = 32;
$jane->age = 30;

$john->sex = Person::MALE;
$jane->sex = Person::FEMALE;

echo '<pre>';

echo $john->talk() . PHP_EOL;
echo $jane->talk() . PHP_EOL;

$john->getMarried($jane);

$billy = new Person();
$billy->name = "Billy";
$billy->age = 0;

$john->childs[] = $billy;
$jane->childs[] = $billy;

echo $billy->talk();

//EXAMPLES OF ERRORS
//
//$billy->name = 10;
//$mary = new stdclass();
//$billy->spouse = $mary;
//
//class Player extends Person {
//	public function TZString_talk() {
//		return $this->age;
//	}
//}
