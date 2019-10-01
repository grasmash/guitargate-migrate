<?php

namespace Drupal\gg_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for user accounts.
 *
 * @MigrateSource(
 *   id = "guitargate_user"
 * )
 */
class GuitargateUser extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('users')
      ->condition('uid', 0, '>')
      ->condition('uid', 1, '<>');
    $query->leftJoin('field_data_field_full_name', 'name', 'users.uid = name.entity_id');
    $query->leftJoin('field_data_field_user_location', 'location', 'users.uid = location.entity_id');
    $query->leftJoin('field_data_field_guitar', 'guitar', 'users.uid = guitar.entity_id');
    $query->leftJoin('field_data_field_twitter_handle', 'twitter', 'users.uid = twitter.entity_id');
    $query->leftJoin('field_data_field_biography', 'bio', 'users.uid = bio.entity_id');
    // @codingStandardsIgnoreStart
    // $query->leftJoin('field_data_field_email_subscription', 'email', 'users.uid = email.entity_id');.
    // @codingStandardsIgnoreEnd
    $query->leftJoin('field_data_field_user_level', 'level', 'users.uid = level.entity_id');

    $query
      ->fields('users', array_keys($this->baseFields()))
      ->fields('name', ['field_full_name_value'])
      ->fields('location', ['field_user_location_value'])
      ->fields('guitar', ['field_guitar_value'])
      ->fields('twitter', ['field_twitter_handle_value'])
      ->fields('bio', ['field_biography_value'])
      // ->fields('email', ['field_email_subscription_value'])
      ->fields('level', ['field_user_level_value']);

    // Picture.
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['field_full_name_value'] = $this->t('Full name');
    $fields['field_user_location_value'] = $this->t('User location');
    $fields['field_guitar_value'] = $this->t('Guitar');
    $fields['field_twitter_handle_value'] = $this->t('Twitter handle');
    $fields['field_music_liked_value'] = $this->t('Music liked');
    $fields['field_biography_value'] = $this->t('Biography');
    // $fields['field_email_subscription'] = $this->t('Email subscription');.
    $fields['field_user_level_value'] = $this->t('User level');

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function baseFields() {
    $fields = [
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'created' => $this->t('Account created date UNIX timestamp'),
      'access' => $this->t('Last access UNIX timestamp'),
      'login' => $this->t('Last login UNIX timestamp'),
      'status' => $this->t('Blocked/Allowed'),
      'timezone' => $this->t('Timeone offset'),
      'init' => $this->t('Initial email address used at registration'),
      'picture' => $this->t('Picture'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get music liked. We do this in prepareRow() rather than query() because
    // there may be multiple values per user. A simple join is insufficient.
    $music_liked = $this->select('field_data_field_music_liked', 'music')
      ->fields('music', ['field_music_liked_value'])
      ->condition('entity_id', $row->getSourceProperty('uid'), '=')
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('field_music_liked_value', $music_liked);
    return parent::prepareRow($row);
  }

}
