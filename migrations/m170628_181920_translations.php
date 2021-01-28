<?php

use yii\db\Migration;

class m170628_181920_translations extends Migration
{
    public function up()
    {
        $this->createTable('translation', [
            'id' => $this->primaryKey(),
            'lang' => $this->string(10)->notNull()->unique(),
            'msg_1_hello' => $this->text()->notNull()->comment('"Looking for Survey" message'),
            'msg_2_closed' => $this->text()->notNull()->comment('"Survey closed" html message'),
        ]);

        $this->batchInsert('translation', ['lang', 'msg_1_hello', 'msg_2_closed'], [
            ['en', 'Looking for Survey', '<h1>Survey closed</h1><p>Thank you for joining our survey, but unfortunately it is now closed. Please look out for other surveys in the future.</p>'],
            ['ru', 'Ищем опрос', '<h1>Опрос завершен</h1><p>К сожалению данный опрос уже завершен. Не расстраивайтесь, в ближайшем будущем обязательно будет проведен новый.</p>'],
            ['vi', 'Tìm kiếm khảo sát', '<h1>Khảo sát đã đóng</h1><p>Cám ơn bạn đã tham gia cuộc khảo sát của chúng tôi, nhưng rất tiếc cuộc khảo sát bị dừng tại đây. Xin hãy tham gia các cuộc khảo sát khác trong thời gian tới.</p>'],
        ]);
    }

    public function down()
    {
        $this->dropTable('translation');
    }
}
