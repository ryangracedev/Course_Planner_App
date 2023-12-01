from playwright.sync_api import sync_playwright, Playwright, Browser, Page

import re

def scrape_course_descs(page: Page):

    # get all course blocks
    course_blocks = page.locator(".courseblock").all()

    # start with blank course description
    course_descs = []

    for course_block in course_blocks:
        # start with blank course desc
        course_desc = ""

        # get all course description text
        details = course_block.locator(".text, .courseblockextra").filter(has_not_text=re.compile("^\\s*$")).all()
        for detail in details:

            # skip the (LEC: x); has class detail-inst_method
            if detail.get_attribute("class").find("detail-inst_method") != -1:
                continue

            # append text to course description
            course_desc += detail.inner_text().strip() + "\n"

        # append course description to list
        course_descs.append(course_desc)

    # return the course descriptions for the subject area
    return course_descs


def scrape_subject_links(page: Page):
    # get all links on the page
    links = page.get_by_role("link").all()

    # extract href from all page links
    all_hrefs = [link.get_attribute("href") for link in links]

    # filter all the hrefs to get only links to subject area pages
    p = re.compile("^/undergraduate-calendar/course-descriptions/[a-zA-Z0-9]+/$")
    filtered_hrefs = [href for href in all_hrefs if p.match(href)]

    # remove duplicate entries
    result = []
    [result.append(x) for x in filtered_hrefs if x not in result]

    # return the set of links
    return result

def run(playwright: Playwright):
    # Launch browser for scraping
    browser = playwright.chromium.launch(headless=True)

    context = browser.new_context()

    # open a new page in the browser context
    page = context.new_page()

    # goto subject areas page
    page.goto("https://calendar.uoguelph.ca/undergraduate-calendar/course-descriptions/")

    # get all links to course descriptions
    subject_links = scrape_subject_links(page)
    # print(subject_links)

    with open("course_desc.txt", "w") as output_file:

        # scrape course descriptions by subject page
        for i, link in enumerate(subject_links, start=1):

            print(f"scraping {i}/{len(subject_links)}: https://calendar.uoguelph.ca{link}")

            page.goto(f"https://calendar.uoguelph.ca{link}")
            course_descs = scrape_course_descs(page)

            output_file.write("\n".join(course_descs) + "\n")

    page.close()


if __name__ == '__main__':
    with sync_playwright() as playwright:
        run(playwright)