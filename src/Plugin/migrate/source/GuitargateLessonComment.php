<?php

namespace Drupal\gg_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Lesson content comments.
 *
 * @MigrateSource(
 *   id = "guitargate_lesson_comment"
 * )
 */
class GuitargateLessonComment extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('comment', 'legacy_comments');
    $query->leftJoin('field_data_comment_body', 'body', 'legacy_comments.cid = body.entity_id');
    $query->join('node', 'node', 'legacy_comments.nid = node.nid');

    $query
      ->condition('node.type', 'lesson')
      ->fields('legacy_comments', array_keys($this->baseFields()))
      ->fields('body', ['comment_body_value'])
      ->orderBy('pid', 'ASC');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['comment_body_format'] = $this->t('Comment body format');

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function baseFields() {
    $fields = [
      'cid' => $this->t('Comment ID'),
      'pid' => $this->t('Parent comment ID in case of comment replies'),
      'name' => $this->t('Comment name (if anon)'),
      'mail' => $this->t('Comment email (if anon)'),
      'uid' => $this->t('User id'),
      'nid' => $this->t('Node id'),
      'subject' => $this->t('Comment subject'),
      'created' => $this->t('Comment created'),
      'changed' => $this->t('Comment changed'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'cid' => [
        'type' => 'integer',
        'alias' => 'legacy_comments',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if ($row->getSourceProperty('name') == 'mpalm') {
      $row->setSourceProperty('comment_body_format', 'rich_text');
    }
    return parent::prepareRow($row);
  }

}
