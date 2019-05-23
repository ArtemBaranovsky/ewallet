<?php

namespace common\modules\controllers;

use yii\web\Controller;
use common\models\Currency;
use common\models\ExchangeRates;


/**
 * Default controller for the `yii2-get-currency-rates` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetRates()
    {
        
    		$model = new Currency;	//['\common\models']
    		$modelExchange = new ExchangeRates;	//['\common\models']
            $modelExchange::find()->all();
            $blockState = Currency::find()->select('block_change')->asArray()->all();
            unset($blockState[0]);
            // die(var_dump('<pre>',ExchangeRates::find()->joinWith('currency')->select('block_change')->asArray()->all(),'</pre>'));
            // die(var_dump('<pre>',Currency::find()->select('block_change')->asArray()->all(),'</pre>'));
    		$cryptoCurrencies = $model->getCurrencyTitles();
    		$fromDB = $modelExchange->getLastRatesFromDB();
    		$lastRatesFromDB = $fromDB['lastRatesFromDB'];
    		$updateTime = $fromDB['updateTime'];


        return $this->render('get-rates', ['model' => $cryptoCurrencies, /*'exchangeRates' => $modelExchange->getExchangeRates(),*/ 'lastRatesFromDB' => $lastRatesFromDB, 'updateTime' => $updateTime, 'blockState' => $blockState]);
    }

    public function actionGetApi(){
    		$model = new Currency;	//['\common\models']
            $symbols = \Yii::$app->request->post('symbols');
    		// $cryptoCurrencies = $model->getCurrencyTitles();
    		// $symbols = implode(',', $cryptoCurrencies);//	 multiple (comma-separated) e.g. BTC,DOGE
            $currency_url = "http://api.coinlayer.com/api/live?access_key=9ffa18a3de7467e66273f6e7484fb746&target=USD&symbols={$symbols}";
    		// $currency_url = "http://api.coinlayer.com/api/live?access_key=2662d865660f33044d955b13e1502011&target=USD&symbols={$symbols}";
				$content = file_get_contents($currency_url);
				$d = json_decode($content, true);

				
				$rates =  (array_column($d['rates'], null));	// сохраняем ассоциативный массив без ключей				
				/*$oldRates = */ExchangeRates::saveLastRates($rates);
				$d = ['timestamp' =>$d['timestamp'], 'rates' => $d['rates']/*, 'oldRates' => $oldRates*/];
				return json_encode($d);
    }


		public static function saveLastRates($rates)
		{
				$model = new ExchangeRates;
        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
				// $model->save($rates);
				// var_dump($model->getErrors());
				// die('stopped in saveLastRates');
				$model->saveLastRates($rates);
		}

}