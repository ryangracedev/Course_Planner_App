import csv
import re


class Course:
    def __init__(self, code="", name="", availability="", weight=0.0, description="", offerings="", equates="", restrictions="", department="", locations="", prerequisites_raw="", prerequisites="", credit_req=0):
        self.code = code  # course code - ABCD*1234
        self.name = name  # the display name of the course
        # which semester(s) the course is typically offered
        self.availability = availability
        self.weight = weight  # how many credits the course is worth
        self.description = description  # details of what is taught in the course
        # any additional ways the course may be taught (distance ed, etc)
        self.offerings = offerings
        # other course(s) this course is equal in content to
        self.equates = equates
        # other course(s) this course can not be taken if already completed
        self.restrictions = restrictions
        self.department = department  # the school in charge of facilitating the course
        self.locations = locations  # campus locations the course is available
        # the human readable display text of pre-reqs
        self.prerequisites_raw = prerequisites_raw
        self.prerequisites = prerequisites  # the vba parsable pre-reqs
        self.credit_req = credit_req  # how many credits are required to take the course


def print_course_list(course_list):
    for course in course_list:
        print_course(course)


def print_course(course):
    print("code: " + course.code)
    print("name: " + course.name)
    print("availability: " + course.availability)
    print("weight: " + str(course.weight))
    print("description: " + course.description)
    print("offerings: " + course.offerings)
    print("equates: " + course.equates)
    print("restrictions: " + course.restrictions)
    print("department: " + course.department)
    print("locations: " + course.locations)
    print("credit req: " + course.credit_req)
    print("prerequisites: None" if len(course.prerequisites) ==
          0 or course.prerequisites[-1] is None else "prerequisites: " + course.prerequisites[-1])
    print("\n")


def parse_simple_and(courses):
    course_prereqs = []

    for course in courses:
        course_prereqs.append(course.strip())

    return course_prereqs


def parse_or(line):
    all_or_conditions = re.split(" *or *", line)

    # TODO: Add check that list contains only course codes

    if (all_or_conditions == None):
        return ""
    return "1 " + " ".join(all_or_conditions)

    orMatch = re.search(r'or', line)
    if orMatch is None:
        return ""
    else:
        span = orMatch.span()
        line.rfind("(", 0, span[0])

        before = re.match(r'[\(,]{1}(.[^\(,]?)*?(?=or)', line)
        if before is None:
            before = re.match(r'^(.[^\(,]?)*?(?=or)', line)

        after = re.search(r'(?<=or).*[\),]|(?<=or).*', line)

        # [\(,]{1}(.[^\(,]?)*?(?=or) look back
        # ^(.[^\(,]?)*?(?=or) look back until start of line

        # (?<=or).*[\),]|(?<=or).* look ahead until ) , or end of string

        # print(before.group())
        parsed_prereqs = ''
        if after is None:
            print("NO AFTER FOUND")
        else:
            # print(after.group())
            parsed_prereqs = '1 ' + before.group().strip() + ' ' + after.group().strip()

    return parsed_prereqs


def parse_of(line):
    m = re.match(r'(?P<n>[0-9]+) *of *(?P<c>.+)', line)
    if (m == None):
        return ""

    # print(m.group('n'))
    # print(m.group('c'))

    course_codes = re.findall(r'[A-Z]+\*[0-9]+', str(m.groups('c')))
    if (course_codes == None):
        return ""

    return m.group('n') + " " + " ".join(course_codes)


def parse_and(line):
    m = re.findall(r'([^,]+\*[0-9]+)(?>, )*', line)
    if (m == None):
        return ""

    return str(len(m)) + " " + " ".join(m)


def parse_standalone(line):
    return line.strip().replace('.', '')


prereq_parsers = {
    r'(?P<a>.+) *or *(?P<b>.+)': parse_or,
    r'(?P<n>[0-9]+) *of *(?P<c>.+)': parse_of,
    r'[A-Z]+\*[0-9]+': parse_standalone,
    # r'(?P<c>[A-Z]+\*[0-9]+)(?>, )*': parse_and
}

# x of a, b, (c, d), e
# ([0-9]+) *of *(.+) \(.+\) .+
# (BIOL*1070, BIOL*1090), (1of BIOM*3010, HK*3401, HK*3501, ZOO*2090)
# BIOC*2580, (1 of BIOM*3200, HK*3810, HK*3940, (ZOO*3200, ZOO*3210), ZOO*3600)
# 2 BIOC*2580 (2 1 BIOM*3200 HK*3810 HK*3940 (2 ZOO*3200 ZOO*3210) ZOO*3600)
# 2 BIOC*2580 , ( 1 of BIOM*3200, HK*3810, HK*3940, ( 2 ZOO*3200 , ZOO*3210 ) , ZOO*3600 )
# BIOC*2580 , ( 1 of BIOM*3200, HK*3810, HK*3940, ( ZOO*3200 , ZOO*3210 ) , ZOO*3600 )
# 0         0 1 1                                 2 2        2 2        2 1 1        1
# 2 (BIOL*1070 BIOL*1090)


def split_raw_prereq(line: str):
    split_prereqs = []
    split = ''
    for char in line:
        if char == '(' or char == ')':
            split = split.strip()
            if len(split) != 0:
                split_prereqs.append(split)
            split_prereqs.append(char)
            split = ''
            continue
        elif char == ',':
            split = split.strip()
            if re.match(r'[0-9]+\.[0-9]+ [C,c]redits', split):
                split = split + char
                continue
            elif re.match(r'([0-9]+) *of *(.+)', split) is None:
                if len(split) != 0:
                    split_prereqs.append(split)
                split_prereqs.append(char)
                split = ''
                continue

        if re.match(r'[0-9]+\.[0-9]+ [C,c]redits,* * including', split):
            split = ''
            continue

        split = split + char

    split = split.strip()
    if len(split) != 0:
        split_prereqs.append(split)

    return split_prereqs, get_nesting_levels(split_prereqs)


def get_nesting_levels(split_prereqs):
    nesting_levels = []
    nest_level = 0
    for split in split_prereqs:
        if split == '(':
            nest_level += 1

        nesting_levels.append(nest_level)

        if split == ')':
            nest_level -= 1

    return nesting_levels


def find_opening_bracket(element_index, nesting_levels, split_prereqs):
    element_nesting_level = nesting_levels[element_index]
    for i in range(element_index, -1, -1):
        if split_prereqs[i] == '(' and nesting_levels[i] == element_nesting_level:
            return i

    return -1


def set_and_conditions(split_prereqs, nesting_levels):
    # x, (a, b), z
    # 'x', ',', '(', 'a', ',', 'b', ')', ',', 'z'
    # 0    0    1    1    1    1    1    0    0

    parsed_comnmas = []  # index of the commas we have parsed through already
    index_offset = 0
    parsed_prereqs = split_prereqs.copy()
    parsed_nesting_levels = nesting_levels.copy()
    for i in range(0, len(split_prereqs)):
        and_count = 0
        if split_prereqs[i] == ',' and i not in parsed_comnmas:
            comma_nesting_level = nesting_levels[i]
            and_count = 2
            for j in range(i + 1, len(split_prereqs)):
                if split_prereqs[j] == ',' and nesting_levels[j] == comma_nesting_level:
                    and_count += 1
                    parsed_comnmas.append(j)
                elif split_prereqs[j] == ')' and nesting_levels[j] == comma_nesting_level:
                    break

            bracket_index = find_opening_bracket(
                i + index_offset, parsed_nesting_levels, parsed_prereqs)
            if bracket_index == -1:
                parsed_prereqs.insert(0, str(and_count))
                parsed_nesting_levels.insert(0, comma_nesting_level)
            else:
                parsed_prereqs.insert(bracket_index + 1, str(and_count))
                parsed_nesting_levels.insert(
                    bracket_index + 1, comma_nesting_level)
            index_offset += 1

    return parsed_prereqs, parsed_nesting_levels


def set_or_conditions(split_prereqs, nesting_levels):
    # x, (a, b), z
    # 'x', ',', '(', 'a', ',', 'b', ')', ',', 'z'
    # 0    0    1    1    1    1    1    0    0

    parsed_ors = []  # index of the 'or' elements we have parsed through already
    index_offset = 0
    parsed_prereqs = split_prereqs.copy()
    parsed_nesting_levels = nesting_levels.copy()
    for i in range(0, len(split_prereqs)):
        if split_prereqs[i] == 'or' and i not in parsed_ors:
            or_nesting_level = nesting_levels[i]
            or_count = 1
            comma_index = -1
            for j in range(i + 1, len(split_prereqs)):
                if split_prereqs[j] == 'or' and nesting_levels[j] == or_nesting_level:
                    parsed_ors.append(j)
                elif split_prereqs[j] == ')' and nesting_levels[j] == or_nesting_level:
                    break
                elif split_prereqs[j] == ',' and nesting_levels[j] == or_nesting_level:
                    print('breaking on comma at index : ' + str(j))
                    comma_index = j
                    break

            bracket_index = find_opening_bracket(
                i + index_offset, parsed_nesting_levels, parsed_prereqs)
            if bracket_index == -1:
                parsed_prereqs.insert(0, str(or_count))
                parsed_nesting_levels.insert(0, or_nesting_level)

                if comma_index != -1:
                    parsed_prereqs.insert(0, '(')
                    parsed_prereqs.insert(comma_index + 1, ')')
                    parsed_nesting_levels.insert(0, -1)
                    parsed_nesting_levels.insert(comma_index + 1, -1)
                    index_offset += 2
            else:
                parsed_prereqs.insert(bracket_index + 1, str(or_count))
                parsed_nesting_levels.insert(
                    bracket_index + 1, or_nesting_level)

                if comma_index != -1:
                    parsed_prereqs.insert(bracket_index + 1, '(')
                    parsed_prereqs.insert(comma_index + 1, ')')
                    parsed_nesting_levels.insert(bracket_index + 1, -1)
                    parsed_nesting_levels.insert(comma_index + 1, -1)
                    index_offset += 2
            index_offset += 1

    return parsed_prereqs, get_nesting_levels(parsed_prereqs)


def set_conditions(split_prereqs, nesting_levels):
    print("raw split prereqs: " + '   '.join(split_prereqs))
    print("raw nesting levels: " + '   '.join(str(x) for x in nesting_levels))
    new_split_prereqs, new_nesting_levels = set_or_conditions(
        split_prereqs, nesting_levels)
    print("split prereqs after parsing or: " + '   '.join(new_split_prereqs))
    print("nesting levels after parsing or: " + '   '.join(str(x)
          for x in new_nesting_levels))
    new_split_prereqs, new_nesting_levels = set_and_conditions(
        new_split_prereqs, new_nesting_levels)
    print("split prereqs after parsing and: " + '   '.join(new_split_prereqs))
    print("nesting levels after parsing and: " + '   '.join(str(x)
          for x in new_nesting_levels))

    return new_split_prereqs, new_nesting_levels

# ([0-9]+) *of *(.+) \(.+\) .+
# (BIOL*1070, BIOL*1090), (1of BIOM*3010, HK*3401, HK*3501, ZOO*2090)
# BIOC*2580, (1 of BIOM*3200, HK*3810, HK*3940, (ZOO*3200, ZOO*3210), ZOO*3600)
# 2 BIOC*2580 (2 1 BIOM*3200 HK*3810 HK*3940 (2 ZOO*3200 ZOO*3210) ZOO*3600)
# 2 BIOC*2580 , ( 1 of BIOM*3200, HK*3810, HK*3940, ( 2 ZOO*3200 , ZOO*3210 ) , ZOO*3600 )
# BIOC*2580 , ( 1 of BIOM*3200, HK*3810, HK*3940, ZOO*3600, ( ZOO*3200 , ZOO*3210 ) )
# 0         0 1 1                                 2 2        2 2        2 1 1        1

# 1 of a, b, c, f, ( d , e ) , ) , h
# 0 0 1 1
#                        ^


def fix_prereq_order(split_prereqs, nesting_levels):
    ordered_prereqs = split_prereqs.copy()
    ordered_nesting_levels = nesting_levels.copy()
    for i in range(0, len(split_prereqs)):
        if re.match(r'[0-9]+ *of *.+', split_prereqs[i]) is not None and split_prereqs[i].endswith(','):
            bracket_nesting_level = nesting_levels[i + 1]
            remaining_elements_index = -1
            for j in range(i + 1, len(split_prereqs)):
                if split_prereqs[j] == ')' and nesting_levels[j] == bracket_nesting_level:
                    remaining_elements_index = j + 1
                    break

            while remaining_elements_index < len(ordered_prereqs):
                if ordered_prereqs[remaining_elements_index] == ')' and ordered_nesting_levels[remaining_elements_index] == ordered_nesting_levels[i]:
                    break

                if ordered_prereqs[remaining_elements_index] == ',':
                    ordered_prereqs.pop(remaining_elements_index)
                    ordered_nesting_levels.pop(remaining_elements_index)
                else:
                    ordered_prereqs[i] = ordered_prereqs[i] + ' ' + \
                        ordered_prereqs.pop(remaining_elements_index) + ','

            ordered_prereqs[i] = ordered_prereqs[i].strip()

    return ordered_prereqs, get_nesting_levels(ordered_prereqs)


def parse_prereqs(line):
    parsed_prereqs = []
    i = 0

    bracket_groups = re.findall(
        r"(?<=\()(?:[^()]*|\([^()]*\))*(?=\))|[^()]+", line)
    # recursively break apart brackets

    prereqs_string = ""

    if len(bracket_groups) > 1:
        # recursive call for each unprocessed group
        for group in bracket_groups:
            # and condition
            if group.endswith(', '):
                if len(group) > 2:
                    parsed_prereqs.append('2')
                else:
                    parsed_prereqs.insert(i - 1, '2')
                i += 1
            parsed_prereqs.append(parse_prereqs(group).strip())
            i += 1

        prereqs_string = " ".join(parsed_prereqs)
        return prereqs_string.strip()

    if len(bracket_groups) == 1:
        for regexp, process_func in prereq_parsers.items():
            if (re.match(regexp, line)):
                return process_func(line).strip()

    return prereqs_string

# x of ()
# x or y
# (x or y)
# (x), (y)
# (x, y)
# ... including x
# x, y, z
# 1 of x, y, z
# (x of )
# x or [(x or y), (1 of x, y, z)] -> 1 x (2 (1 x y) (1 x y z))


def parse_complex_prereq(line):
    return [parse_or(line)]


# Check for remaining course info
def get_course_info(line, course):

    # Regex for getting course credit requirement
    cr_pattern = r'(\d+(.\d{2})?|\d+) [C,c]redit'

    if "Offering(s)" in line:
        course.offerings = line.strip().replace('\n', '').replace('Offering(s): ', '')
    elif "Prerequisite(s)" in line:
        clean_line = line.strip().replace('\n', '').replace(
            'Prerequisite(s): ', '').replace('[', '(').replace(']', ')')
        course.prerequisites_raw = clean_line
        credit_req_match = re.match(cr_pattern, clean_line)
        if credit_req_match is not None:
            course.credit_req = float(
                credit_req_match.group().lower().replace('credit', ''))
    elif "Equate(s)" in line:
        course.equates = line.strip().replace('\n', '').replace('Equate(s): ', '')
    elif "Restriction(s)" in line:
        course.restrictions = line.strip().replace(
            '\n', '').replace('Restriction(s): ', '')
    elif "Department(s)" in line:
        course.department = line.strip().replace(
            '\n', '').replace('Department(s): ', '')
    elif "Location(s)" in line:
        course.locations = line.strip().replace('\n', '').replace('Location(s): ', '')


def get_availability(line, course):
    if any(season in line for season in ("Fall", "Winter", "Summer")):
        course.availability = line.strip().replace('\n', '')
        return True
    elif any(char in line for char in ("[", "]")):
        return False


def get_description(line, course):
    if not any(keyword in line for keyword in ["Offering(s)", "Prerequisite(s)", "Equate(s)", "Restriction(s)", "Department(s)", "Location(s)"]):
        course.description = line.strip().replace('\n', '')
        return True
    else:
        return False


def parser():
    file_path = input("enter relative path of input course list: ")
    file = open(file_path, "r")
    fileContents = file.readlines()

    courseList = []
    index = 0
    overallIndex = 0
    course = Course()

    for line in fileContents:

        # Get course code
        if index == 0:
            course.code = line.strip().replace('\n', '')
        # Get course name
        if index == 1:
            course.name = line.strip().replace('\n', '')
        # Get course availability
        if index == 2:
            # Check if availability exists
            if get_availability(line, course) == False:
                # If not, skip
                index += 1
        # Get course weight
        if index == 3:
            course.weight = float(line.strip().replace('\n', '').replace(
                '[', '').replace(']', ''))  # ideally just substring here
        # Get course description
        if index == 4:
            # Check if description exists
            if get_description(line, course) == False:
                # If not, skip
                index += 1
        # Get advanced course info
        if index >= 5:
            get_course_info(line, course)

        index += 1
        overallIndex += 1
        if line == '\n' or overallIndex == len(fileContents):
            courseList.append(course)
            course = Course()
            index = 0

    # reformatting of prerequisites for csv file
    for course in courseList:
        if course.prerequisites_raw is None:
            print("isNone")
        prereqs = []

        # Check if course is not in prereq
        if '*' in course.prerequisites_raw:
            # If so, extract each course for csv
            split_prereqs, nesting_levels = split_raw_prereq(
                course.prerequisites_raw.replace('[', '(').replace(']', ')'))
            split_prereqs, nesting_levels = fix_prereq_order(
                split_prereqs, nesting_levels)
            # print('after fixing prereq order:')
            # print('   '.join(test))
            print('parsing prereqs for course ' + course.code)
            split_prereqs, nesting_levels = set_conditions(
                split_prereqs, nesting_levels)
            print('')

            # x, y
            # 'x', ',' 'y'
            # '2', 'x', ',', 'y'

            for i in range(0, len(split_prereqs)):
                # print('split: ' + split_prereqs[i])
                if split_prereqs[i] != '(' and split_prereqs[i] != ')' and not split_prereqs[i].isdigit():
                    split_prereqs[i] = parse_prereqs(split_prereqs[i])
                elif split_prereqs[i] == ',':
                    split_prereqs[i] = ''

            course.prerequisites = ' '.join(split_prereqs).replace(
                '  ', ' ').replace(' )', ')').replace('( ', '(')

    # Headers for csv
    header = ['code', 'name', 'availability', 'weight', 'description', 'offerings', 'equates', 'restrictions',
              'departments', 'locations', 'credit prerequisites', 'raw prerequisites', 'prerequisites']

    # Write course information to csv
    with open('courses.csv', 'w', encoding='UTF8') as f:
        writer = csv.writer(f, quotechar='"', quoting=csv.QUOTE_ALL)

        # write the header
        writer.writerow(header)

        for course in courseList:
            data = [course.code, course.name, course.availability, course.weight, course.description, course.offerings, course.equates,
                    course.restrictions, course.department, course.locations, course.credit_req, course.prerequisites_raw, course.prerequisites]
            # write the data
            writer.writerow(data)

    file.close()


if __name__ == '__main__':
    parser()
