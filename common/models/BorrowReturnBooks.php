<?php
namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "borrow_return_books".
 *
 * @property int $id
 * @property string $card_number 卡号
 * @property string $bar_code 条码号
 * @property int $operation 借还操作:1借，0还
 * @property int $library_id 图书馆ID
 * @property int $user_id 操作员ID
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $status 状态
 */
class BorrowReturnBooks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'borrow_return_books';
    }
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_number', 'bar_code', 'operation', 'library_id'], 'required'],
            [['operation', 'library_id', 'user_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['card_number'], 'string', 'max' => 64],
            [['bar_code'], 'string', 'max' => 128],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_number' => '卡号',
            'bar_code' => '条码号',
            'operation' => '借还操作:1借，0还',
            'library_id' => '图书馆ID',
            'user_id' => '操作员ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '状态',
        ];
    }


    static function getOperationOption($key = null)
    {
        $arr = array(
            1 => '借书',
            0 => '还书',
        );
        return $key === null ? $arr : (isset($arr[$key]) ? $arr[$key] : '');
    }
    static function getOperation($model)
    {
        return self::getOperationOption($model->operation);
    }
    public static function getReaderInfoAjax($cardnumber_or_barcode)
    {
        //这里模糊查询
        //输入可以是卡号，也可以是书的条码
        $input_ret1 = BorrowReturnBooks::findOne(['card_number' => $cardnumber_or_barcode, 'operation' => 1]);
        $input_ret2 = BorrowReturnBooks::findOne(['bar_code' => $cardnumber_or_barcode, 'operation' => 1]);
        if (!empty($input_ret1)) {
            $reader = Reader::findOne(['card_number' => $input_ret1->card_number]);
        } else if (!empty($input_ret2)) {
            $reader = Reader::findOne(['card_number' => $input_ret2->card_number]);
        } else {
            return \yii\helpers\Json::encode(['code' => -2]);
        }
        $reader_type = ReaderType::findOne(['id' => $reader->reader_type_id]);
        $card_status_txt = Reader::getCardStatusOption($reader->card_status);
        $card_status = $reader->card_status ? "<span class=\"label label-success\">" . $card_status_txt . "</span>" : "<span class=\"label label-danger\">" . $card_status_txt . "</span>";
        $reader_info = [
            'card_number' => $reader->card_number,
            'reader_name' => $reader->reader_name,
            'card_status' => $card_status,
            'validity' => date('Y-m-d', $reader->validity),
            'id_card' => $reader->id_card,
            'reader_type_id' => Reader::getReaderTypeOption($reader->reader_type_id),
            'gender' => Reader::getGenderOption($reader->gender),
            'deposit' => $reader->deposit,
            'creditmoney' => $reader->creditmoney,
            'mobile' => $reader->mobile,
            'max_borrowing_number' => $reader_type->max_borrowing_number,
            'max_debt_limit' => $reader_type->max_debt_limit,
        ];
        if (empty($reader)) {
            return \yii\helpers\Json::encode(['code' => -1]);
        } else {
            return \yii\helpers\Json::encode(['code' => 0, 'reader_info' => $reader_info]);
        }
    }
    public static function getBooksInfoAjax($cardnumber)
    {
        $due_date = 0;
        $count = BorrowReturnBooks::find()->where(['card_number' => $cardnumber, 'operation' => 1])->count();
        $brbs = BorrowReturnBooks::find()->where(['card_number' => $cardnumber, 'operation' => 1])->all();

        $reader = Reader::findOne(['card_number' => $cardnumber]);
        if(!empty($reader))
        {
          $reader_type = ReaderType::findOne(['id' => $reader->reader_type_id]);
          if(!empty($reader_type))
          {
            $due_date = $reader_type->max_return_time * 24 * 3600; //seconds
          }
        }

        //array_push
        $info = [];
        foreach ($brbs as $brb) {
            $bookcopy = BookCopy::findOne(['bar_code' => $brb->bar_code]);
            $book = Book::findOne(['id' => $bookcopy->book_id]);
            $collection_place = CollectionPlace::findOne(['id' => $bookcopy->collection_place_id]);
            $user = User1::findOne(['id' => $brb->user_id]);
            $item = [
                'bar_code' => $brb->bar_code,
                'created_at' => date('Y-m-d', $brb->created_at),
                'title' => $book->title,
                'isbn'  => $book->isbn,
                'publisher'  => $book->publisher,
                'call_number'  => $book->call_number,
                'collection_place' => $collection_place->title,
                'operator'  => $user->username,
                'due_date' => (intval($due_date) == 0) ? '-' : date('Y-m-d', $brb->created_at + $due_date),
            ];
            array_push($info, $item);
        }
        $borrow_return_books = [
            'count' => $count,
            'info'  => $info,
        ];
        if (empty($borrow_return_books)) {
            return \yii\helpers\Json::encode(['code' => -1]);
        } else {
            return \yii\helpers\Json::encode(['code' => 0, 'borrow_return_books' => $borrow_return_books]);
        }
    }
    public function getReader()
    {
        return $this->hasOne(Reader::className(), ['card_number' => 'card_number']);
    }

    public function getBookCopy()
    {
      return $this->hasOne(BookCopy::className(), ['bar_code' => 'bar_code']);
    }

    
}