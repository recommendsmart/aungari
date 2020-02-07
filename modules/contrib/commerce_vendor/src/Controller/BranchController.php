<?php

namespace Drupal\commerce_vendor\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\commerce_vendor\Entity\BranchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BranchController.
 *
 *  Returns responses for Branch routes.
 */
class BranchController extends ControllerBase implements ContainerInjectionInterface {


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
   * Constructs a new BranchController.
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
   * Displays a Branch revision.
   *
   * @param int $branch_revision
   *   The Branch revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($branch_revision) {
    $branch = $this->entityTypeManager()->getStorage('branch')
      ->loadRevision($branch_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('branch');

    return $view_builder->view($branch);
  }

  /**
   * Page title callback for a Branch revision.
   *
   * @param int $branch_revision
   *   The Branch revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($branch_revision) {
    $branch = $this->entityTypeManager()->getStorage('branch')
      ->loadRevision($branch_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $branch->label(),
      '%date' => $this->dateFormatter->format($branch->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Branch.
   *
   * @param \Drupal\commerce_vendor\Entity\BranchInterface $branch
   *   A Branch object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BranchInterface $branch) {
    $account = $this->currentUser();
    $branch_storage = $this->entityTypeManager()->getStorage('branch');

    $langcode = $branch->language()->getId();
    $langname = $branch->language()->getName();
    $languages = $branch->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $branch->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $branch->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all branch revisions") || $account->hasPermission('administer branch entities')));
    $delete_permission = (($account->hasPermission("delete all branch revisions") || $account->hasPermission('administer branch entities')));

    $rows = [];

    $vids = $branch_storage->revisionIds($branch);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\commerce_vendor\BranchInterface $revision */
      $revision = $branch_storage->loadRevision($vid);
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
        if ($vid != $branch->getRevisionId()) {
          $link = $this->l($date, new Url('entity.branch.revision', [
            'branch' => $branch->id(),
            'branch_revision' => $vid,
          ]));
        }
        else {
          $link = $branch->link($date);
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
                Url::fromRoute('entity.branch.translation_revert', [
                  'branch' => $branch->id(),
                  'branch_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
                Url::fromRoute('entity.branch.revision_revert', [
                  'branch' => $branch->id(),
                  'branch_revision' => $vid,
                ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.branch.revision_delete', [
                'branch' => $branch->id(),
                'branch_revision' => $vid,
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

    $build['branch_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
