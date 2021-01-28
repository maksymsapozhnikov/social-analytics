<?php
namespace app\components\formatters;

/**
 * Class JavascriptFormatter
 */
class JavascriptFormatter implements \yii\web\ResponseFormatterInterface
{
    protected function getRandomModified($tag)
    {
        $magicDate = 1497890066 - hexdec(substr($tag, 0, 5));

        return date('D, d M Y H:i:s \G\M\T', $magicDate);
    }

    public function format($response)
    {
        $response->content = $response->data;

        $response->getHeaders()->add('Content-Type', 'application/javascript');

        if ($response->statusCode == 200) {
            if (isset(\Yii::$app->params['etag'])) {
                $etag = \Yii::$app->params['etag'];
                $response->getHeaders()->add('Pragma', 'cache');
                $response->getHeaders()->add('Cache-Control', 'private, only-if-cached , max-age=2592000');
                $response->getHeaders()->add('Last-Modified', $this->getRandomModified($etag));
                $response->getHeaders()->add('Etag', '"' . $etag . '"');
                $response->getHeaders()->add('Expires', date('D, d M Y H:i:s \G\M\T', time() + 2592000));
            } else {
                $response->getHeaders()->add('Pragma', 'no-cache');
                $response->getHeaders()->add('Cache-Control', 'no-cache, must-revalidate');
                $response->getHeaders()->add('Expires', date('D, d M Y H:i:s \G\M\T', time() - 604800));
            }
            $response->getHeaders()->add('Content-length', strlen($response->content));
        }
    }
}
