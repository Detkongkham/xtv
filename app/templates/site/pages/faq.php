<?php
/**
 * FAQ (faq.html) from the faqs table (/admin/faqs).
 * @var App\Publish\SiteView $v
 */
$v->meta('faq', $v->t('faq'), $v->t('faq_lead'));
$qa = array_map(static fn (array $f): array => [$v->tr($f, 'question'), $v->withContact($v->raw($f, 'answer'))], $v->data->faqs());
?>
<?= $v->partial('page-head', ['title' => $v->t('faq'), 'lead' => $v->t('faq_lead')]) ?>
<section class="sec sec--tight"><div class="wrap" style="max-width:1040px">
  <?php if ($qa !== []): ?>
  <div class="faq rv">
    <?php foreach ($qa as $i => [$q, $a]): ?>
    <details<?= $i === 0 ? ' open' : '' ?>><summary><?= $q ?><?= $v->ic('chev') ?></summary><p><?= $a ?></p></details>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <div class="aside-box rv" style="margin-top:56px;text-align:center">
    <h2 class="h4"><?= $v->t('faq_more') ?></h2>
    <div class="btns" style="justify-content:center;margin-top:16px"><a class="btn" href="contact.html"><?= $v->t('contact') ?></a><a class="btn btn--ghost" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->t('whatsapp') ?></a></div>
  </div>
</div></section>
