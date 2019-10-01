<?php

namespace Drupal\gg_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Lesson content.
 *
 * @MigrateSource(
 *   id = "guitargate_lesson"
 * )
 */
class GuitargateLesson extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node')
      ->condition('type', 'lesson');
    $query->leftJoin('field_data_body', 'body', 'node.nid = body.entity_id');
    $query->leftJoin('field_data_field_lesson_number', 'lesson_number', 'node.nid = lesson_number.entity_id');
    $query->leftJoin('field_data_field_lesson_level', 'lesson_level', 'node.nid = lesson_level.entity_id');

    $query
      ->fields('node', array_keys($this->baseFields()))
      ->fields('lesson_number', ['field_lesson_number_value'])
      ->fields('lesson_level', ['field_lesson_level_value'])
      ->fields('body', ['body_value']);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['body_value'] = $this->t('Body');
    $fields['images'] = $this->t('Fret diagram images.');
    $fields['field_lesson_number_value'] = $this->t('Lesson number.');
    $fields['field_lesson_level_value'] = $this->t('Lesson level.');

    // field_lesson_video
    // field_jam_tracks
    // field_slide_title
    // field_lesson_faq.
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

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $image_fids = $this->select('field_data_field_fret_diagrams', 'diagrams')
      ->fields('diagrams', ['field_fret_diagrams_fid'])
      ->condition('entity_id', $row->getSourceProperty('nid'), '=')
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('images', $image_fids);

    $pdf_fids = $this->select('field_data_field_fret_diagram_pdf', 'pdfs')
      ->fields('pdfs', ['field_fret_diagram_pdf_fid'])
      ->condition('entity_id', $row->getSourceProperty('nid'), '=')
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('pdfs', $pdf_fids);

    return parent::prepareRow($row);
  }

}
