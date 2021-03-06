<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Database\InvalidDataTest.
 */

namespace Drupal\system\Tests\Database;

use Drupal\Core\Database\IntegrityConstraintViolationException;

/**
 * Tests handling of some invalid data.
 *
 * @group Database
 */
class InvalidDataTest extends DatabaseTestBase {
  /**
   * Tests aborting of traditional SQL database systems with invalid data.
   */
  function testInsertDuplicateData() {
    // Try to insert multiple records where at least one has bad data.
    try {
      db_insert('test')
        ->fields(array('name', 'age', 'job'))
        ->values(array(
          'name' => 'Elvis',
          'age' => 63,
          'job' => 'Singer',
        ))->values(array(
          'name' => 'John', // <-- Duplicate value on unique field.
          'age' => 17,
          'job' => 'Consultant',
        ))
        ->values(array(
          'name' => 'Frank',
          'age' => 75,
          'job' => 'Singer',
        ))
        ->execute();
      $this->fail('Insert succeeded when it should not have.');
    }
    catch (IntegrityConstraintViolationException $e) {
      // Check if the first record was inserted.
      $name = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 63))->fetchField();

      if ($name == 'Elvis') {
        if (!Database::getConnection()->supportsTransactions()) {
          // This is an expected fail.
          // Database engines that don't support transactions can leave partial
          // inserts in place when an error occurs. This is the case for MySQL
          // when running on a MyISAM table.
          $this->pass("The whole transaction has not been rolled-back when a duplicate key insert occurs, this is expected because the database doesn't support transactions");
        }
        else {
          $this->fail('The whole transaction is rolled back when a duplicate key insert occurs.');
        }
      }
      else {
        $this->pass('The whole transaction is rolled back when a duplicate key insert occurs.');
      }

      // Ensure the other values were not inserted.
      $record = db_select('test')
        ->fields('test', array('name', 'age'))
        ->condition('age', array(17, 75), 'IN')
        ->execute()->fetchObject();

      $this->assertFalse($record, 'The rest of the insert aborted as expected.');
    }
  }

}
