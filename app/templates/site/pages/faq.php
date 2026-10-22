<?php
/**
 * FAQ (faq.html). The questions move to the faqs table in phase 3 (P3-D2).
 * @var App\Publish\SiteView $v
 */
$v->meta('faq', $v->t('faq'), $v->t('faq_lead'));
$qa = [[$v->t('fq1'), $v->t('fa1')], [$v->t('fq2'), $v->t('fa2')], [$v->t('fq3'), $v->t('fa3')], [$v->t('fq4'), $v->t('fa4')], [$v->t('fq5'), $v->address()]];
?>
<?= $v->partial('page-head', ['title' => $v->t('faq'), 'lead' => $v->t('faq_lead')]) ?>
<section class="sec sec--tight"><div class="wrap" style="max-width:1040px">
  <div class="faq rv">
    <?php foreach ($qa as $i => [$q, $a]): ?>
    <details<?= $i === 0 ? ' open' : '' ?>><summary><?= $q ?><?= $v->ic('chev') ?></summary><p><?= $a ?></p></details>
    <?php endforeach ?>
  </div>
  <div class="aside-box rv" style="margin-top:56px;text-align:center">
    <h2 class="h4"><?= $v->t('faq_more') ?></h2>
    <div class="btns" style="justify-content:center;margin-top:16px"><a class="btn" href="contact.html"><?= $v->t('contact') ?></a><a class="btn btn--ghost" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->t('whatsapp') ?></a></div>
  </div>
</div></section>
