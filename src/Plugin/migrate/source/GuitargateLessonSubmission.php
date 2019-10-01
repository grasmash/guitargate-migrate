<?php

namespace Drupal\gg_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Lesson Submission content.
 *
 * @MigrateSource(
 *   id = "guitargate_lesson_submission"
 * )
 */
class GuitargateLessonSubmission extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node')
      ->condition('type', 'lesson_submission');
    $query->leftJoin('field_data_body', 'body', 'node.nid = body.entity_id');
    $query->leftJoin('field_data_field_submission_related_lesson', 'lesson', 'node.nid = lesson.entity_id');
    $query->leftJoin('field_data_field_user_video', 'video', 'node.nid = video.entity_id');

    $query
      ->fields('node', array_keys($this->baseFields()))
      ->fields('body', ['body_value'])
      ->fields('lesson', ['field_submission_related_lesson_nid'])
      ->fields('video', ['field_user_video_input']);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['field_user_video_input'] = $this->t('Video');
    $fields['field_submission_related_lesson_nid'] = $this->t('Lesson');

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function baseFields() {
    $fields = [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Node revision ID'),
      'title' => $this->t('Node Title'),
      'uid' => $this->t('Author user ID'),
      'created' => $this->t('Created date UNIX timestamp'),
      'changed' => $this->t('Updated date UNIX timestamp'),
      'status' => $this->t('Node publication status'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
      ],
    ];
  }

}
