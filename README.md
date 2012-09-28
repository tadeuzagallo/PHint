#PHint#

Simple lib to type hint in PHP

It's composed for the type constans, at this moment they are:
	- TZString
	- TZInteger
	- TZFloat
	- TZArray
	- TZDictionary
	- TZBool
	- TZVoid

(But you can declare any property or method to use any class, you just need to use its name as a string instead of of the type constant)

And the class that all you objects should extend, named TZObject.
The class does not provide any method to you, just set up the PHP magic methods to validate the types.

She the example.php to see how to declare properties and methods.

Thanks! =D
