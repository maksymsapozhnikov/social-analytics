<?php

use yii\db\Migration;
use \app\models\translation\SourceMessage;

class m170911_200813_changeTranslations extends Migration
{
    const CATEGORY = 'survey-process';

    protected static $translations = [
        'Looking for Survey' => [
            ['en', 'Looking for Survey'],
            ['ru', 'Ищем опрос'],
            ['vi', 'Tìm kiếm khảo sát'],
            ['cs', 'Hledáte průzkum'],
            ['pl', 'Szukam badania'],
            ['th', 'กำลังหาแบบสอบถามหรือไม่'],
            ['km', 'កំពុងស្វែងរកការស្ទង់មតិ'],
            ['ms', 'Sedang mencari soal selidik'],
            ['zh', '寻找问卷调查'],
            ['ar', 'البحث عن إستطلاع'],
        ],
        'Survey closed' => [
            ['en', 'Survey closed'],
            ['ru', 'Опрос завершен'],
            ['vi', 'Khảo sát đã đóng'],
            ['cs', 'Průzkum byl uzavřen'],
            ['pl', 'Ankieta zamknięta'],
            ['th', 'ปิดแบบสำรวจแล้ว'],
            ['km', null],
            ['ms', null],
            ['zh', null],
            ['ar', 'إستطلاع مغلق'],
        ],
        'Thank you for joining our survey, but unfortunately it is now closed. Please look out for other surveys in the future.' => [
            ['en', 'Thank you for joining our survey, but unfortunately it is now closed. Please look out for other surveys in the future.'],
            ['ru', 'К сожалению данный опрос уже завершен. Не расстраивайтесь, в ближайшем будущем обязательно будет проведен новый.'],
            ['vi', 'Cám ơn bạn đã tham gia cuộc khảo sát của chúng tôi, nhưng rất tiếc cuộc khảo sát bị dừng tại đây. Xin hãy tham gia các cuộc khảo sát khác trong thời gian tới.'],
            ['cs', 'Děkujeme za vstup do našeho průzkumu, ale bohužel je nyní uzavřen. Vezměte prosím v úvahu další průzkumy v budoucnu.'],
            ['pl', 'Dziękujemy za udział w badaniu, ale niestety jest ono zamknięte. Zapraszamy za jakiś czas.'],
            ['th', 'ขอบคุณที่เข้าร่วมการทำแบบสอบถามของเรา แต่น่าเสียดายที่ตอนนี้ปิดไปแล้ว โปรดรอแบบสอบถามอื่น ๆ ในอนาคต'],
            ['km', 'សូមអរគុណចំពោះការចូលរួមក្នុងការស្ទង់មតិរបស់យើងប៉ុន្តែជាអកុសលឥឡូវនេះវាត្រូវបានបិទ។ សូមពិនិត្យមើលការស្ទង់មតិផ្សេងទៀតនាពេលអនាគត។'],
            ['ms', 'Terima kasih kerana menyertai soal selidik kami, tetapi ia sudah ditutup. Sila sertai kami lagi dalam soal selidik di masa depan.'],
            ['zh', '感谢您加入我们的调查，但不幸的是现在已经不接受多余的答复。 请关注未来的其他调查。'],
            ['ar', 'نشكرك على الانضمام إلى استطلاع الرأي، ولكن للأسف تم إغلاقه الآن. يرجى البحث عن استطلاعات أخرى في المستقبل.'],
        ],
        'The phone number doesn\'t exist. Please correct the number and check again.' => [
            ['en', 'The phone number doesn\'t exist. Please correct the number and check again.'],
            ['ru', 'Номер телефона недоступен. Пожалуста, убедитесь, что номер телефона указан правильно.'],
            ['vi', 'Số điện thoại này không tồn tại. Xin vui lòng ghi nhận và kiểm tra lại'],
            ['cs', null],
            ['pl', 'Numer telefonu nie istnieje. Proszę poprawić numer i spróbować ponowanie.'],
            ['th', 'ไม่มีหมายเลขโทรศัพท์นี้ โปรดแก้ไขตัวเลขและตรวจสอบอีกครั้ง'],
            ['km', 'លេខទូរស័ព្ទមិនមានទេ។ សូមកែតម្រូវលេខហើយពិនិត្យម្តងទៀត។'],
            ['ms', 'Nombor telefon ini tidak wujud.  Sila betulkan nombor dan semak semula.'],
            ['zh', '电话号码不存在。 请更正号码并再次检查。'],
            ['ar', null],
        ],
        'Unable to top up this phone, should use {CURR} as a currency' => [
            ['en', 'Unable to top up this phone, should use {CURR} as a currency'],
            ['ru', 'Невозможно пополнить данный телефон, необходимо чтобы валюта телефона была {CURR}'],
            ['vi', 'Không thể nạp tiền cho số điện thoại này. Xin vui lòng cung cấp mã trả trước.'],
            ['cs', null],
            ['pl', null],
            ['th', 'ไม่สามารถเติมเงินให้กับโทรศัพท์เครื่องนี้ได้ โปรดระบุหมายเลขโทรศัพท์มือถือที่ถูกต้อง'],
            ['km', 'មិនអាចបញ្ចូលលុយទូរស័ព្ទនេៈបាន។សូមផ្តល់ លេខទូរស័ព្ទដែលអាចបញ្ចូលលុយបាន សូមសរសេរអក្សរដិត'],
            ['ms', 'Tidak boleh tambah nilai untuk telefon ini.  Sila berikan nombor telefon mudah alih yang sah.'],
            ['zh', '无法充值这部手机。 请提供有效的预付手机号码（不是后付费的）。'],
            ['ar', 'غير قادر على شحن رصيد هذا الهاتف،المرجو إدخال رقم هاتف مدفوع مسبقا.'],
        ],
        'Please provide prepaid phone number' => [
            ['en', 'Please provide prepaid phone number'],
            ['ru', null],
            ['vi', null],
            ['cs', null],
            ['pl', null],
            ['th', null],
            ['km', null],
            ['ms', null],
            ['zh', null],
            ['ar', null],
        ],
        'This phone number has been payed already for this survey' => [
            ['en', 'This phone number has been payed already for this survey'],
            ['ru', null],
            ['vi', null],
            ['cs', null],
            ['pl', null],
            ['th', null],
            ['km', null],
            ['ms', null],
            ['zh', null],
            ['ar', null],
        ],
    ];

    public function safeUp()
    {
        foreach(self::$translations as $sourceMessage => $translations) {
            $sourceModel = $this->addSourceMessage($sourceMessage);
            foreach($translations as $translation) {
                $sourceModel->addTranslation($translation[0], $translation[1]);
            }
        }

        $this->dropTable('translation');
    }

    protected function addSourceMessage($message)
    {
        $sourceMessage = new SourceMessage([
            'category' => self::CATEGORY,
            'message' => $message,
        ]);

        $sourceMessage->save();

        if (!$sourceMessage->id) {
            throw new \Exception('Error add translastion for: ' . $message);
        }

        return $sourceMessage;
    }

    public function safeDown()
    {
        echo "m170911_200813_changeTranslations cannot be reverted.\n";

        return false;
    }
}
