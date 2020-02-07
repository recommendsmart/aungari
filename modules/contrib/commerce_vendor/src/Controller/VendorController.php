<?php

namespace Drupal\commerce_vendor\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\commerce_vendor\Entity\VendorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VendorController.
 *
 *  Returns responses for Vendor routes.
 */
class VendorController extends ControllerBase implements ContainerInjectionInterface {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new VendorController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays a Vendor revision.
   *
   * @param int $vendor_revision
   *   The Vendor revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($vendor_revision) {
    $vendor = $this->entityTypeManager()->getStorage('vendor')
      ->loadRevision($vendor_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('vendor');

    return $view_builder->view($vendor);
  }

  /**
   * Page title callback for a Vendor revision.
   *
   * @param int $vendor_revision
   *   The Vendor revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($vendor_revision) {
    $vendor = $this->entityTypeManager()->getStorage('vendor')
      ->loadRevision($vendor_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $vendor->label(),
      '%date' => $this->dateFormatter->format($vendor->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Vendor.
   *
   * @param \Drupal\commerce_vendor\Entity\VendorInterface $vendor
   *   A Vendor object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(VendorInterface $vendor) {
    $account = $this->currentUser();
    $vendor_storage = $this->entityTypeManager()->getStorage('vendor');

    $langcode = $vendor->language()->getId();
    $langname = $vendor->language()->getName();
    $languages = $vendor->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $vendor->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $vendor->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all vendor revisions") || $account->hasPermission('administer vendor entities')));
    $delete_permission = (($account->hasPermission("delete all vendor revisions") || $account->hasPermission('administer vendor entities')));

    $rows = [];

    $vids = $vendor_storage->revisionIds($vendor);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\commerce_vendor\VendorInterface $revision */
      $revision = $vendor_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)
          ->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $vendor->getRevisionId()) {
          $link = $this->l($date, new Url('entity.vendor.revision', [
            'vendor' => $vendor->id(),
            'vendor_revision' => $vid,
          ]));
        }
        else {
          $link = $vendor->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
                Url::fromRoute('entity.vendor.translation_revert', [
                  'vendor' => $vendor->id(),
                  'vendor_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
                Url::fromRoute('entity.vendor.revision_revert', [
                  'vendor' => $vendor->id(),
                  'vendor_revision' => $vid,
                ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.vendor.revision_delete', [
                'vendor' => $vendor->id(),
                'vendor_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['vendor_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
