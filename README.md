# Solution Overview

### Usage

Run `php bin/parser.php`. Running without any arguments will display the usage instructions:

````
usage: parser.php [--json] [--parser PARSER] [--] <url>
````

- The `--json` option is used to select JSON output as per the spec.
- The `--parser` option is used to dynamically select the type of parser to be used. It must
 be accompanied by a valid value. `--parser foo` will cause an error (unknown parser) and
 display a list of the valid values.
- `<url>` is the URL to parse.

### Structure

- The two basic, orthogonal requirements from the spec are represented by `UriParserInterface`
 (parsing) and `FormatterInterface` (output).
- There are two functional parsers: `PhpInternalUriParser` is based on `parse_url` and 
 `Rfc3986NonValidatingParser` is based on a regular expression from the RFC. A third parser,
 `Rfc3986ValidatingParser` (which is intended to validate URLs in full compliance with the RFC)
 is not functional but included in the project as a teaser.
- The classes `Uri` and `QueryComponent` are value objects related to the above, intended to
 provide well-defined interfaces for the business domain concepts.
- `JsonFormatter` and `YamlFormatter` are responsible for displaying the output in the
 selected format.
- `BootstrapFactory` is a facade encapsulating basic services such as reading and interpreting
 command line arguments.
- `CartesianProductIterator` is the best part for showing off (recursive generator composition?
 deep dive in the interplay between PHP internals and PhpUnit? there's something for everyone!)
 but is completely unrelated to the problem at hand (used only to achieve better unit tests).
 
### Testing

There are extensive unit tests for all components of the project, with the following exceptions:

- `BootstrapFactory` is not tested (too much).
- `JsonFormatter` and `YamlFormatter` are not tested (one-liners and the backing code itself
 is being tested very well by the vendors).

However, even the classes not being tested have been written such that effective testing is
possible and easy.

There is also a functional test that runs `parser.php` and examines its behavior to confirm
it adheres to the spec.

### Third-party dependencies

The implementation uses these well-known third party libraries:

- Symfony Console: provides convenient command line parsing and encapsulated output streams.
- Symfony YAML: so that I didn't have to implement a custom "human readable" output mode --
 the sample output in the spec is almost exactly what this produces out of the box.
- Symfony Process: convenient OO wrapper around the necessary facilities to test the actual
 deliverable as an individual process and confirm it agrees with the spec.
- PhpUnit: for unit and functional testing.

### Other resources used 

The implementation is based on the relevant parts of RFC 3986 (included in project repo as `data/rfc3986.txt` for convenience).
