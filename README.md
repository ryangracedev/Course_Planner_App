# Sprint 2

## Webscraper

Using Python and the Playwright, course descriptions defined in the [course calendar](https://calendar.uoguelph.ca/undergraduate-calendar/) are scraped and output to `course_desc.txt`.\
An empty line will be inserted at the end of each course to indicate the end of the record. Leading and trailing whitespace will be removed.

### Running

**Prerequisite**: Python 3.xx, pip and virtualenv are installed. Using a virtual environment is recommended to avoid installing Python packages globally and to containerize your development environment. Instructions for working with [virtualenv](https://www.freecodecamp.org/news/how-to-setup-virtual-environments-in-python/).\
 **Windows + Mac**: After activating virtual environment:

```
pip install playwright
playwright install
cd Source/python/webscraper
python3 course_desc.py
```

**Linux**: Incompatible with school servers as playwright is a non-standard python package. Don't have the necessary permissions as a student to install it through Python package manager(pip).

### Output Format

```
ACCT*1220
Introductory Financial Accounting
Summer, Fall, and Winter
[0.50]
This course will introduce students to the fundamental concepts and practices of Financial Accounting. Students are expected to become adept at performing the functions related to the accounting cycle, including the preparation of financial statements.
Offering(s): Also offered through Distance Education format.
Restriction(s): ACCT*2220. This is a Priority Access Course. Enrolment may be restricted to particular programs or specializations. See department for more information.
Department(s): Department of Management
Location(s): Guelph

ACCT*1240
Applied Financial Accounting
Winter Only
[0.50]
This course requires students to apply the fundamental principles emanating from accounting's conceptual framework and undertake the practice of financial accounting. Students will become adept at performing the functions related to each step in the accounting cycle, up to and including the preparation of the financial statements and client reports. Students will also develop the skills necessary for assessing an organization's system of internal controls and financial conditions.
Offering(s): Also offered through Distance Education format.
Prerequisite(s): ACCT*1220 or ACCT*2220
Restriction(s): ACCT*2240. This is a Priority Access Course. Enrolment may be restricted to particular programs or specializations. See department for more information.
Department(s): Department of Management
Location(s): Guelph
```

## Parser

Asks for the file path to `course_desc.txt`, each course in the file is parsed and information is put into its own `Course` object. Using the information in the course object, data is written to a CSV file.

**Note**: Initially, prerequisites are consumed as a singular string. Each course code found in a given string is extracted into it's own column/cell and finally the original string is stored in its own column as well.

> **Original String**: ACCT*1220 or ACCT*2220\
> **Individual columns for prerequisites**: ACCT*1220, ACCT*2220, ACCT*1220 or ACCT*2220

### Example CSV File: [Courses.csv](https://gitlab.socs.uoguelph.ca/cis3760_f23/f23_cis3760_302/-/blob/sprint1/Source/python/parser/courses.csv)

### Running

**Prerequisite**: Python 3.xx\
Compatible on Windows, Mac, Linux

```
cd Source/parser
python3 course_parser.py
[File Path: "../webscraper/course_desc.txt"]
```

## CLI

Data is read from the CSV file created from parser and course information from each row is put into its own `Course` object to be leveraged by the CLI.\
CLI can perform 2 actions given a course code:

1. List the course's prerequisites
2. List all the course's information

### Running

**Prerequisite**: Python 3.xx\
Compatible on Windows, Mac, Linux

```
cd Source/python
python3 main.py
```

## VBA Course Recommendations

The `coursesMacro.xslm` file contains a program that when a user enters their completed courses, they can get a highlighted list of acceptable courses that they can take next semester.

### Usage

1. Open `coursesMacro.xslm`
2. Open "input" tab
3. Enter course codes of completed courses in column "A" under the header
4. Click Run
5. Browse highlighted courses in spreadsheet

### Limitations

Currently the VBA program does not recognize the "including" keyword. On courses that use this keyword the program may not recognize that the courses listed are requirements.

The program also does not play nicely when blurbs of text are in the prerequisite list. These can cause unpredictable behaviour and may suggest the incorrect courses for the student
