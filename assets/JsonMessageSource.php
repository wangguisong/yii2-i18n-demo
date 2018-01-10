<?php
/**
 * Created by PhpStorm.
 * User: 38212
 * Date: 2018/1/9
 * Time: 15:49
 */

namespace wallet\assets;

use Yii;
use yii\i18n\MessageSource;

class JsonMessageSource extends MessageSource
{
    public $language;

    public $basePath = '@app/assets/locale';

    public function init()
    {
        parent::init();
        if ($this->language === null) {
            $this->language = Yii::$app->language;
        }
    }

    protected function getMessageFilePath($category, $language)
    {
        $messageFile = Yii::getAlias($this->basePath) . "/$language/$category.json";
        return $messageFile;
    }

    protected function loadMessagesFromFile($messageFile)
    {
        if (is_file($messageFile)) {
            $jsonStr = file_get_contents($messageFile);
            try{
                $messages = json_decode($jsonStr, true);
                return $messages;
            }catch (\Exception $e){
                Yii::error("The message file  '$messageFile' is not a json ".$e->getMessage());
                return null;
            }
        } else {
            Yii::error("The message file  '$messageFile' is not exists ");
            return null;
        }
    }

    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($category, $language);
        $messages = $this->loadMessagesFromFile($messageFile);
        return $messages;
    }

    public function translate($category, $message, $language)
    {
        return $this->translateMessage($category, $message, $language);
    }
}