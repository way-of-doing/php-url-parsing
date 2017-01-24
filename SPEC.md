# URL parser problem

Please, prepare an URL parser in PHP.

Your solution should meet the following criteria:

- Please provide a CLI command that would accept one string argument (the URL) and output the parsed URL or report an error.
- Based on the command’s arguments, the output should be either a human-readable print or a JSON (see examples below).
- Prepare unit tests for your code.
- Use PHP7.

Anything else is up to you. Please do your best, try to implement the best code practices.

Do not hesitate to use the Internet but please include links to all the resources you used in your solution.

#### Output examples

````
> php parser.php "https://www.google.com/?q=OLX&lang=de"
scheme: https
host: www.google.com
path: /
arguments:
	q = OLX
	lang = de
> echo $?
0
````

````
> php parser.php --json "https://www.google.com/?q=OLX&lang=de"
{"scheme":"https","host":"www.google.com","path":"/","arguments":{"q":"OLX","lang":"de"}}
> echo $?
0
````

````
> php parser.php --json "/www.google.com/?q=OLX&lang=de"
{"path":"/www.google.com/","arguments":{"q":"OLX","lang":"de"}}
> echo $?
0
````

````
> php parser.php --json "http://?"
Incorrect URL.
> echo $?
1
````

The output can differ a bit, e.g. contain more or less information about the URL.

In case of a success, the output should be written to the standard output and the script should end with exit code 0. In case of an error, the error message should be written to the standard error output and the script should end with exit code 1.
