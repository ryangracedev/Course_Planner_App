import mariadb
import pandas as pd
import numpy as np
import re

try:
    conn = mariadb.connect(
        user="root",
        password="",
        host="localhost",
        port=3306,
        database="course_data"
    )
except mariadb.Error as err:
    print(f"Error connecting: {err}")
    exit(1)

cursor = conn.cursor()

# Function to get the ID of a course based on subject area and course number
def get_course_id(course_sbj_area: str, course_num: int) -> int | None:
    cursor.execute(
        "SELECT id FROM courses WHERE subject_area=? AND number=?",
        (course_sbj_area, course_num)
    )

    course_id = cursor.fetchone()
    if(course_id is None):
        return None
    return course_id[0]

# Function to insert a new offering for a course
def insert_offering(course_id: int, semester: str):
    cursor.execute(
        "INSERT INTO offerings (course_id, semester) VALUES (?,?)",
        (course_id, semester)
    )

# Function to insert equated courses
def insert_equated(course_id: int, equated_course_code: str):

    sbj_area, course_num = equated_course_code.split("*")

    equated_id = get_course_id(sbj_area, course_num)
    if(equated_id is not None):
        cursor.execute(
            "INSERT INTO equates (course_id, equated_id) VALUES (?,?)",
            (course_id, equated_id)
        )


# Function to insert prerequisites
def insert_prereq(prereq_str: str, parent_id = None) -> int | None:

    prereq_str = prereq_str.strip()

    if(prereq_str == ""):
        return None

    num_req = int(prereq_str[0])

    bracket_groups = re.findall(
        r"(?<=\()(?:[^()]*|\([^()]*\))*(?=\))|[^()]+", prereq_str[1:])
    bracket_groups = [group.strip() for group in bracket_groups if group.strip() != ""]
    print(bracket_groups)

    # insert requirement node
    cursor.execute(
        "INSERT INTO requirements (parent_id, num_required) VALUES (?,?)",
        (parent_id, num_req)
    )

    req_id = cursor.lastrowid

    if len(bracket_groups) == 1:

        num_written = 0

        for course_code in re.findall(r"[A-Z]+\*[0-9]+", bracket_groups[0]):
            sbj_area, course_num = course_code.split("*")
            pre_req_id = get_course_id(sbj_area, course_num)

            if(pre_req_id is not None):
                cursor.execute(
                    "INSERT INTO course_requirements (course_id, requirement_id) VALUES (?,?)",
                    (pre_req_id, req_id)
                )
                num_written = num_written + 1
        
        if(num_req > num_written):
            num_req = num_written
            cursor.execute(
                "UPDATE requirements SET num_required=? WHERE id=?",
                (num_written, req_id)
            )
    else:
        for group in bracket_groups:
            insert_prereq(group, req_id)

    return req_id


def insert_course(course: pd.Series):

    course_data = course[["subject_area", "number", "name", "weight", "description", "department", "location", "prerequisites", "credits_required"]]
    
    cursor.execute(
        "INSERT INTO courses (subject_area, number, name, weight, description, department, location, prerequisites, credits_required) VALUES (?,?,?,?,?,?,?,?,?)",
        course_data.values.tolist()
    )

    course_id = cursor.lastrowid

    # print(course)
    # print(course["availability"])
    
    if(re.search("Fall", course["availability"], re.IGNORECASE) != None):
        insert_offering(course_id, "F")
    if(re.search("Winter", course["availability"], re.IGNORECASE) != None):
        insert_offering(course_id, "W")
    if(re.search("Summer", course["availability"], re.IGNORECASE) != None):
        insert_offering(course_id, "S")

if __name__ == "__main__":

    courses_df = pd.read_csv("./parser/courses.csv", quotechar="\"", delimiter=",", on_bad_lines="warn")

    # clean up data
    courses_df[['subject_area', 'number']] = courses_df['code'].str.split("*", expand=True)
    courses_df.rename(columns={
            "departments": "department",
            "locations": "location",
            "prerequisites": "parsed_prereqs",
            "raw prerequisites": "prerequisites",
            "credit prerequisites": "credits_required"
        }, inplace=True)
    
    # for index, course in courses_df[courses_df.isna().any(axis=1)].iterrows():
    #     print(course)

    # set defaults
    courses_df["prerequisites"].replace(np.nan, "", inplace=True)
    courses_df["description"].replace(np.nan, "", inplace=True)
    courses_df["department"].replace(np.nan, "", inplace=True)
    courses_df["availability"].replace(np.nan, "", inplace=True)
    courses_df["availability"].replace(np.nan, "", inplace=True)
    courses_df["equates"].replace(np.nan, "", inplace=True)
    courses_df["parsed_prereqs"].replace(np.nan, "", inplace=True)

    for index, course in courses_df.iterrows():
        insert_course(course)
        pass

    for index, course in courses_df.iterrows():

        # cursor.execute(
        #     "SELECT id FROM courses WHERE subject_area=? AND number=?",
        #     (course['subject_area'], course['number'])
        # )

        course_id = get_course_id(course['subject_area'], course['number'])

        for equated_course in re.findall(r"[A-Z]+\*[0-9]+", course['equates']):

            if(course_id != None):
                insert_equated(course_id, equated_course)

        req_id = insert_prereq(course['parsed_prereqs'])

        if(req_id is not None):
            cursor.execute(
                "UPDATE courses SET requirements_id=? WHERE id=?",
                (req_id, course_id)
            )

    conn.commit()

    conn.close()
