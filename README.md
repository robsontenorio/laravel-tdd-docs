# WIP

- Very first trying, so there is no elegant solution implemeted yet. You can help :)
- Improving english translations.

# Package

TDD living docs for Laravel. Generate docs from comments at your phpunit tests. Make your tests as usual and include only the comments you need to generate the reports.

Develop your software using TDD with clean workflow and get a bonus with zero effort: **living docs**.


# Background

TDD is a great way to develope software. The development workflow is clean and fluid. And, if you use the BDD approach  (Given, When, Then) to do it you have a good code readability, what give us a opportunity to documentate it very easely.

As we know [BEHAT](http://behat.org/en/latest/guides.html) uses BDD approach to write its tests (Given, When, Then). This is a great way to understand what happens to the software.

````
Feature: Listing command
  In order to change the structure of the folder I am currently in
  As a UNIX user
  I need to be able see the currently available files and folders there

  Scenario: Listing two files in a directory
    Given I am in a directory "test"
    And I have a file named "foo"
    And I have a file named "bar"
    When I run "ls"
    Then I should get: "bar"

  [...] another scenarios from this same feature [...]

````

To use BEHAT with Laravel you need a series of settings, have some extra files, and follow a restricted pattern. The way test classes are generated is somewhat confusing, as your app grows.

You can also get a detailed report of your tests when using [plugins](https://github.com/dutchiexl/BehatHtmlFormatterPlugin) to export results to html, **what is great!**



# Well, and if ... :bulb: :bulb: :bulb:

And if we could to have same result (structured tests and reports) with **zero config**, only using Laravel Testing Suite, simpling commenting your testing files?

Well, we develop our software using TDD with clean workflow and get a bonus with almost zero effort: **living docs**.

That is, make your tests as usual and include only the comments you need to generate the reports.


# Install


### Require

```
composer require "robsontenorio/laravel-tdd-docs"
```

### Modify

Add this on `phpunit.xml`

```
<logging>
    <log type="testdox-xml" target="storage/app/testing-docs/report.xml"/>
</logging>
```

### Permissions

Make sure you have permissions to write on `storage/app` folder.


### Run

- Run `phpunit` on project root
- Browse to `/testing-docs` route to see docs.

# Example

## Input

```

<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * 
 * @feature Manage my threads
 * @tag threads
 * 
 * In order to manage my threads 
 * As a regular user from forum
 * I need to have control about my posted threads
 * 
 */
class ThreadTest extends TestCase
{
    public function test_An_authenticated_user_can_create_a_thread()
    {
        /** Given i am a authenticated user */
        $user = factory('App\User')->create();

        /** Given i filled all fields to create a thread */
        $thread = factory('App\Thread')->make();

        /** When request is processed */
        $response = $this->actingAs($user)->post('/api/threads', $thread->toArray());

        /** Then i sould see new thread created */
        $response->assertJsonFragment($thread->toArray());

    }

    public function test_An_authenticated_user_cant_delete_a_thread_from_another_user()
    {
        /** Given i am a authenticated user */
        $user1 = factory('App\User')->create();

        /** Given there is thread created by another user */
        $user2 = factory('App\User')->create();     
        
        // another user. NOTE: this kind comment is not parsed
        $thread = factory('App\Thread')->create(['user_id' => $user->id]);

        /** When request is processed */
        $response = $this->actingAs($user)->delete("/api/threads/{$thread->id}");

        /** Then i sould a error message */
        $response->assertJsonFragment(['error' => 'You can not delete a thread from another user.']);

        /** And response status code is 403 */
        
        // Lets simulate a error in here, we got 200 but, 403 was expected
        $response->assertStatus(403);
    }
}

```
## Output

<img src="example.png">

# How it works

1) It read all testing files from `tests/Feature` folder

2) It searchs for a full `doc block`  on `class` section (feature description) 

3) It search for `inline comments` (steps) on each `method` (scenarios).

4) It compares `phpunit` output generated on `storage/app/testing-docs/report.html` with your `testing files` to highlight the report with failed tests.

5) You can see full report/docs at browsing to `/testing-docs` route.

# Assumptions

## General 

- Each file on `tests/Feature` folder is a `feature`

- Each class `method`  is a `scenario`.

## DocBlock

- The `class` must have a full DocBlock comment 
    - it must have a `@feature` anotation
    - it must have a `@tag` anotation
    - it must have a description body

- Each class `method` must have a `test_` prefix 
    - The `inline comments` inside `methods` must be in this format `/** comment in here */`

 
# Inspiration
- [Behat](http://behat.org/en/latest/guides.html)
- [Behat html formatter plugin](https://github.com/dutchiexl/BehatHtmlFormatterPlugin)


# TODO

- Improve regex, to avoid exceptions when docblocks does not follow pattern proposed.
- Filter by tag
- Graphs
