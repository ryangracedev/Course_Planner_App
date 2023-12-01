import re
from playwright.sync_api import Page, expect
import json;
# "http://localhost"

URL = "https://cis3760f23-10.socs.uoguelph.ca"
# URL = "http://localhost"
#UI Tests

# Adding 1220 to plan and checking if it comes into planned section and is removed from the catalog
def test_add_to_plan(page:Page):
    page.goto(URL + "/planner")

    #"Add To My Plan" should not be visible since they're hidden by the accordian by default
    expect(page.get_by_text("Add To My Plan").locator("nth=0"), "Button \" Add to My Plan\" should not be visible").not_to_be_visible()

    #Clicking the accordian to expose "Add To My Plan" button
    # saved = page.locator("button:near(.accordion-button)").first
    saved = page.locator('#course-1').filter(has=page.get_by_role('button'))

    saved.click()
    
    expect(page.locator('button:has-text("Add To My Plan")').locator("nth=0",),"Button \" Add to My Plan\" should be visible").to_be_visible()

    #Clicking "Add To My Plan" 
    page.locator('button:has-text("Add To My Plan")').locator("nth=0").click()

    #ACCT*1220 should be in plan
    expect(page.locator('#plan > #course-1').filter(has_text="ACCT*1220"),"ACCT*1220 not found in planned courses").to_be_visible()

    #ACCT*1220 should not be in catalog
    expect(page.locator('#catalog > #course-1').filter(has_text="ACCT*1220"), "ACCT*1220 should not have been found in course catalog").not_to_be_visible()

def test_remove_from_plan(page:Page):
    
    #Simulate identical actions from add_to_plan test first
    page.goto(URL + "/planner")

    saved = page.locator('#course-1').filter(has=page.get_by_role('button'))

    saved.click()
    
    page.locator('button:has-text("Add To My Plan")').locator("nth=0").click()

    #checks to make sure that Remove from plan is visible
    expect(page.locator('button:has-text("Remove")').locator("nth=0"),"Button \" Remove\" should be visible").to_be_visible()
    
    #click the remove button
    page.locator('button:has-text("Remove")').locator("nth=0").click()

    #Plan should no longer have ACCT*1220
    expect(page.locator('#plan > #course-1').filter(has_text="ACCT*1220"),"ACCT*1220 should no longer be in planned courses").not_to_be_visible()

    #Catalog should have ACCT*1220
    expect(page.locator('#catalog > #course-1').filter(has_text="ACCT*1220"),"ACCT*1220 not found in course catalog").to_be_visible()

def test_subject_area_filter(page:Page):
    page.goto(URL + "/planner")

    #Check if subject area filter is visible
    expect(page.get_by_text("Subject Area").locator("nth=0"), "Subject Area Filter not on page").to_be_visible()

    #Set filter to agriculture
    page.locator('#subject-select').select_option('AGR')

    #We have to double click since we have 
    page.get_by_role('button', name=re.compile("Apply Filters", re.IGNORECASE)).click()
    page.wait_for_timeout(2000)
    page.get_by_role('button', name=re.compile("Apply Filters", re.IGNORECASE)).click()

    page.screenshot(path="screenshot.png")

    #No accounting courses should be in catalog
    expect(page.locator('#catalog > #course-1').filter(has_text="ACCT*1220").locator("nth=0"),"ACCT*1220 was found in course catalog").not_to_be_visible()

    #Set filter to CIS
    page.locator('#subject-select').select_option('CIS')
    page.get_by_role('button', name=re.compile("Apply Filters", re.IGNORECASE)).click()
    page.wait_for_timeout(2000)
    page.get_by_role('button', name=re.compile("Apply Filters", re.IGNORECASE)).click()

    #CIS courses should be found in course catalog
    expect(page.locator('#catalog > #course-277').filter(has_text="CIS*1050").locator("nth=0"),"CIS*1050 was not found in course catalog").to_be_visible()





#REST API ENDPOINT TESTS
def test_get_subject(page: Page):
    response = page.request.post(URL + "/rest/subject",data={"subject": "CIS"})
    assert response.ok,"Non 200 error code returned"
    assert len(response.json()) == 47, "Expected 47 courses returned {}".format(len(response.json()))


def test_get_subject_invalid_subject(page: Page):
    response = page.request.post(URL + "/rest/subject",data={"subject": "C"}) #testing receive error when subject area < 2
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/subject",data={"subject": "CISSS"}) #testing receive error when subject area > 4
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/subject",data={"subject": "CISS"}) #is within the size limit but not an actual subject
    assert response.ok,"Non 200 error code returned"
    assert len(response.json()) == 0, "Expected 0 courses returned {}".format(len(response.json()))



def test_get_subject_all(page: Page):
    response = page.request.post(URL + "/rest/subject",data={"subject": "all"})
    first_subject = {"subject_area":"ACCT"}
    last_subject = {"subject_area":"ZOO"}
    assert response.ok, "Non 200 error code returned"
    assert response.json()[0] == first_subject, "First subject is not ACCT"
    assert response.json()[len(response.json())-1] == last_subject,"Last subject is not ZOO"
    assert len(response.json()) == 90, "Expected 90 courses returned {}".format(len(response.json()))


def test_get_prereq(page: Page):
    cis2500Res = '[{"id":287,"parent_id":null,"num_required":1,"subject_area":"CIS","number":1300,"name":"Programming"}]'
    response = page.request.post(URL + "/rest/prereq",data={"prereq": "CIS*2500"})
    assert response.ok,"Non 200 error code returned"
    assert response.json() == json.loads(cis2500Res), "CIS*2500 response json does not match"


def test_get_prereq_invalid_input(page: Page):
    response = page.request.post(URL + "/rest/prereq",data={"prereq": "CIS2500"}) #testing receive error when no * in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/prereq",data={"prereq": "C*2500"}) #testing receive error when subject area < 2 in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/prereq",data={"prereq": "CIS*25000"}) #testing receive error when course code >=10000 in course
    assert response.status == 400, "Did not return 400 Bad Request"

    cis1300Res = '[]'
    response = page.request.post(URL + "/rest/prereq",data={"prereq": "CISS*2500"}) #testing that we receive empty object when giving course that's not in database
    assert response.ok,"Non 200 error code returned"
    assert response.json() == json.loads(cis1300Res), "CISS*2500 response json does not return empty object"


def test_get_course(page: Page):
    cis1300Res = '[{"id":280,"subject_area":"CIS","number":1300,"name":"Programming","weight":"0.50","description":"This course examines the applied and conceptual aspects of programming. Topics may include data and control structures, program design, problem solving and algorithm design, operating systems concepts, and fundamental programming skills. This course is intended for students who plan to take later CIS courses. If your degree does not require further CIS courses consider CIS*1500 Introduction to Programming.","department":"School of Computer Science","location":"Guelph","prerequisites":"","requirements_id":null,"credits_required":"0.00"}]'
    response = page.request.post(URL + "/rest/course",data={"course": "CIS*1300"})
    assert response.ok,"Non 200 error code returned"
    assert response.json() == json.loads(cis1300Res), "CIS*1300 response json does not match"


def test_get_course_invalid_course(page: Page):
    response = page.request.post(URL + "/rest/course",data={"course": "CIS1300"}) #testing receive error when no * in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/course",data={"course": "C*1300"}) #testing receive error when subject area < 2 in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/course",data={"course": "CIS*13000"}) #testing receive error when course code >=10000 in course
    assert response.status == 400, "Did not return 400 Bad Request"

    cis1300Res = '[]'
    response = page.request.post(URL + "/rest/course",data={"course": "CISS*1300"}) #testing that we receive empty object when giving course that's not in database
    assert response.ok,"Non 200 error code returned"
    assert response.json() == json.loads(cis1300Res), "CISS*1300 response json does not return empty object"
    

def test_get_course_all(page: Page):
    response = page.request.post(URL + "/rest/course",data={"course": "all"})
    assert response.ok,"Non 200 error code returned"
    assert len(response.json()) == 1913, "Expected 1913 courses returned {}".format(len(response.json()))


def test_get_eligible(page: Page):
    response = page.request.post(URL + "/rest/api", data={"course1": "CIS*1300"})
    courseList = ["CIS*1050", "CIS*1200", "CIS*1250", "CIS*1300", "CIS*1500", "CIS*1910", "CIS*2170", "CIS*2500", "CIS*4450", "CIS*4500", "CIS*4900"]

    allCISCourses = set(["CIS*1050", "CIS*1200", "CIS*1250", "CIS*1300", "CIS*1500", "CIS*1910", "CIS*2030", "CIS*2170", "CIS*2250", 
                     "CIS*2430", "CIS*2500", "CIS*2520", "CIS*2750", "CIS*2910", "CIS*3050", "CIS*3090", "CIS*3110", "CIS*3120", 
                     "CIS*3130", "CIS*3150", "CIS*3190", "CIS*3210", "CIS*3250", "CIS*3260", "CIS*3490", "CIS*3530", "CIS*1050",
                     "CIS*3700", "CIS*3750", "CIS*3760", "CIS*4010", "CIS*4020", "CIS*4030", "CIS*4050", "CIS*4150", "CIS*4250",
                     "CIS*4300", "CIS*4450", "CIS*4500", "CIS*4510", "CIS*4520", "CIS*4650", "CIS*4720", "CIS*4780", "CIS*4800",
                     "CIS*4820", "CIS*4900", "CIS*4910"]);

    assert response.ok,"Non 200 error code returned"
    assert allCISCourses.intersection(set(response.json())) == set(courseList),"Incorrect set of eligible courses returned based on request"


def test_get_eligible_invalid_course(page: Page):
    courseList = ["CIS*1050", "CIS*1200", "CIS*1250", "CIS*1300", "CIS*1500", "CIS*1910", "CIS*4450", "CIS*4500", "CIS*4900"]

    allCISCourses = set(["CIS*1050", "CIS*1200", "CIS*1250", "CIS*1300", "CIS*1500", "CIS*1910", "CIS*2030", "CIS*2170", "CIS*2250", 
                     "CIS*2430", "CIS*2500", "CIS*2520", "CIS*2750", "CIS*2910", "CIS*3050", "CIS*3090", "CIS*3110", "CIS*3120", 
                     "CIS*3130", "CIS*3150", "CIS*3190", "CIS*3210", "CIS*3250", "CIS*3260", "CIS*3490", "CIS*3530", "CIS*1050",
                     "CIS*3700", "CIS*3750", "CIS*3760", "CIS*4010", "CIS*4020", "CIS*4030", "CIS*4050", "CIS*4150", "CIS*4250",
                     "CIS*4300", "CIS*4450", "CIS*4500", "CIS*4510", "CIS*4520", "CIS*4650", "CIS*4720", "CIS*4780", "CIS*4800",
                     "CIS*4820", "CIS*4900", "CIS*4910"]);

    response = page.request.post(URL + "/rest/api",data={"course1": "CIS1300"}) #testing receive error when no * in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/api",data={"course1": "C*1300"}) #testing receive error when subject area < 2 in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/api",data={"course1": "CIS*13000"}) #testing receive error when course code >=10000 in course
    assert response.status == 400, "Did not return 400 Bad Request"

    response = page.request.post(URL + "/rest/api",data={"course1": "CISS*1300"}) #testing that we receive empty object when giving course that's not in database
    assert response.ok,"Non 200 error code returned"
    assert allCISCourses.intersection(set(response.json())) == set(courseList),"Incorrect set of eligible courses returned based on request"