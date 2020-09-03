@local @local_chunkupload @javascript @_file_upload
Feature: Test local_chunkupload

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following config values are set as admin:
      | chunksize | 1 | local_chunkupload |
    And I log in as "teacher1"
    And I visit "/local/chunkupload/tests/testupload.php"

  Scenario: Upload a correct image with multiple chunks
    When I upload the "local/chunkupload/tests/fixtures/correct.png" file to the "test" chunkupload
    And I click on "Save" "button"
    Then I should see "correct.png"
    And I should see "Hash=e1b06fa52a506fb6501b52d3aacd23d7"

  Scenario: Upload a file that is too big
    When I upload the "local/chunkupload/tests/fixtures/toobig.png" file to the "test" chunkupload
    Then I should see "The file you tried to upload is too large for the server to process."

  Scenario: Upload a file that has the wrong format
    When I upload the "local/chunkupload/tests/fixtures/wrongformat.jpg" file to the "test" chunkupload
    Then I should see ".jpg filetype cannot be accepted"
