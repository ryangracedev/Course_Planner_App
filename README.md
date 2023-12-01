![Guelph Campus](https://admission.uoguelph.ca/sites/default/files/images/GeneralBanner5_2018.jpg)

# Sprint 7

## REST API

Our API is accessible at:

> https://cis3760f23-10.socs.uoguelph.ca/rest

Through our POST and PUT requests, it can retreive, add, update or delete data in our database.

A request URL follows the rest structure, meaning that the api will invoke a certain endpoint that you specify in the URL. It will then read your input through the HTTP request's body.

## POST

#### `/course`
Will retrieve all data associated with a course with the code: {code} where the body is:

#### `"course": {courseCode}`

Example:  
> https://cis3760f23-10.socs.uoguelph.ca/rest/course

Request Body:

#### `"course": "CIS*1300"`

The following request body will get all data associated with all courses:

#### `"course": "all"`

---
#### `/subject`

Will retrieve all the courses where the subject is: {subject_area} and the body is:

#### `"subject": {subject_area}`

Example:
>https://cis3760f23-10.socs.uoguelph.ca/rest/subject

Request Body:

#### `"subject": "CIS"`

The following request body will get all data associated with all courses:

#### `"subject": "all"`

---
#### `/prereq`

Will retrieve the prereqs for the course with the code: {code} where the body is:

#### `"prereq": {courseCode}`

Example:  
> https://cis3760f23-10.socs.uoguelph.ca/rest/prereq

Request Body:

#### `"prereq": "CIS*1300"`

---
#### `/api`
The POST request will take prerequisite courses, and return courses one can take. The POST request must have the following body format: 

#### `"course": {courseCode}`

Or if there are multiple then: 

#### `"course1": {courseCode}, "course2": {courseCode}`

The POST request is also accessible through the form at:
https://cis3760f23-10.socs.uoguelph.ca/rest/api?findCourses=True

## PUT
The PUT request must have the following body format:
```
subject_area: {codeSubject},
number: {codeNum},
name: {courseName},
weight: {courseWeight},
description: {courseDesc},
department: {courseDept},
location: {courseLocation},
prerequisites: {coursePrereqs},
requirements_id:0,
credits_required:{reqCreds}
```

### Descriptions 

`{codeSubject}` - the subject part of a course code (e.g, 'CIS' in 'CIS*1300')\
`{codeNum}` - the number part of a course code (e.g, '1300' in 'CIS*1300')\
`{courseName}` - the course's name\
`{courseWeight}` - the course's credit weighting\
`{courseDesc}` - the course's description\
`{courseDept}` - the course's department\
`{courseLocation}` - the course's building location\
`{coursePrereqs}` - the course's prerequisites\
`{reqCreds}` - the amount of completed credits required to take this course

## Running

There are two ways you can run our REST API:

### 1. Using Postman

Postman is both a web based, and desktop based application. It allows us to send specific HTTP requests to our server. Ensure that if the request type requires a body, that you follow the same format as specified above depending on the request type.

---

### 2. Chrome Console

Going to https://cis3760f23-10.socs.uoguelph.ca and inspecting the webpage to open the console allows us to create a request and send it to the server, where it will be handled accordingly. Below are the formats of the requests that are expected from the server:

---

#### POST

As mentioned above, the `/api` POST request must have the following body:

#### `"course": {courseCode}`

Or if there are multiple then: 

#### `"course1": {courseCode}, "course2": {courseCode}`

After sending the request, you'll receive the courses you can take, or you'll receive an appropriate error message.

For example, to find courses you can take, you would inspect the https://cis3760f23-10.socs.uoguelph.ca webpage and send the following request in the inspect console:
```
fetch("https://cis3760f23-10.socs.uoguelph.ca/rest/api", {
  method: "POST",
  body: JSON.stringify({
    "course1": "CIS*2750", "course2": "CIS*3750"
  }),
  headers: {
    "Content-type": "application/json; charset=UTF-8"
  }
})
```

---

#### PUT

As mentioned above, the PUT request must have the following body:
```
subject_area: {codeSubject},
number: {codeNum},
name: {courseName},
weight: {courseWeight},
description: {courseDesc},
department: {courseDept},
location: {courseLocation},
prerequisites: {coursePrereqs},
requirements_id:0,
credits_required:{reqCreds}
```

After sending the request, the course you entered will be updated, or you'll receive an appropriate error message.

For example, to update an existing course's info in the database (in this case, changing CIS*1700's credit weighting to 1.0), you would inspect the https://cis3760f23-10.socs.uoguelph.ca webpage and send the following request in the inspect console:
```
fetch("https://cis3760f23-10.socs.uoguelph.ca/rest/api", {
  method: "PUT",
  body: JSON.stringify({
    subject_area: "CIS",
    number: 1700,
    name: "Intro to Better programming",
    weight: 1.0,
    description: "Learn better programming",
    department: "CEPS",
    location: "MCKN",
    prerequisites: "",
    requirements_id:0,
    credits_required:10
  }),
  headers: {
    "Content-type": "application/json; charset=UTF-8"
  }
})
```


## Database

Data previously in our excel file is now located in the MySQL database on the server. The "courses" table contains the columns:

#### `"subject_area", "number", "name", "weight", "description", "department", "location", "prerequisites", "requirements_id", "credits_required"`

This is where the main information for all the courses is stored and is what our API currently accesses. This was directly imported from our previous excel file.

We also have the "equates" table with the columns:
#### `"course_id", "equated_id"`

The "offerings" table with the columns: 
#### `"id", "course_id", "semester"`

The requirements table with the columns: 
#### `"id", "parent_id", "num_required"`

And the "course_requirements" table with the columns: 
#### `"course_id", "requirement_id"`
