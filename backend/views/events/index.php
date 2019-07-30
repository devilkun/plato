<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;

use common\models\Events;
/* @var $this yii\web\View */
/* @var $searchModel common\models\EventsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '日历事件';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="events-index">
<p>
    <?= Html::button('新增日历事件', ['value' =>  Url::toRoute(['events/create', 'date' => date('Y-m-d'), 't'=>time()]), 'class' => 'btn btn-success', 'id' => 'modalButton']) ?>
</p>


<?php
    Modal::begin([
        'header' => '<h4>新增日历事件</h4>',
        'id' => 'modal',
        'size' => 'modal-lg',
    ]);

    echo "<div id='modalContent'></div>";
    Modal::end();
?>


<?= \yii2fullcalendar\yii2fullcalendar::widget(array(
        'events'=> $events,
        'options' => [
            'lang' => 'zh-cn',
            //... more options to be defined here!
        ],
        ));
    ?>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<!-- 
    <//?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id',
                'headerOptions' => array('style'=>'width:10%;'),
            ],

            'title',
            // 'event_type',
            [
                'attribute' => 'event_type',
                'label' => '事件类型',
                'value'=>function ($model, $key, $index, $column) {
                    return Events::getEventTypeOption($model->event_type);
                },
                'filter'=> Events::getEventTypeOption(),
                'headerOptions' => array('style'=>'width:15%;'),
            ],
            //'description:ntext',
            //'library_id',
            //'user_id',
            'created_at:datetime',
            //'updated_at',
            //'status',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?> -->


</div>


<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
    //alert('ready');
   $(document).on('click','.fc-day', function(){
       //alert('fc-day');
        var date = $(this).attr('data-date');
        var d = new Date();

        $.get('<?=  Url::toRoute(['events/create']) ?>', {'date':date, 't':d.getTime()}, function(data){
            $('#modal').modal('show')
            .find('#modalContent')
            .html(data);
        });
    });

    $('#modalButton').click(function(){
        $('#modal').modal('show')
            .find('#modalContent')
            .load($(this).attr('value'));
    });
})
</script>