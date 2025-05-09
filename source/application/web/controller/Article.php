<?php
namespace app\web\controller;

use app\web\model\Article as ArticleModel;
use app\web\model\article\Category as CategoryModel;


/**
 * 文章控制器
 * Class Article
 * @package app\web\controller
 */
class Article extends Controller
{
    /**
     * 文章首页
     * @return array
     */
    public function index()
    {
        // 文章分类列表
        $categoryList = CategoryModel::getAll();
        return $this->renderSuccess(compact('categoryList'));
    }
    
    /**
     * 文章
     * @return array
     */
    public function categorylist($category_id = 0)
    {
        // 文章分类列表
        $model = new ArticleModel;
        $list = $model->getList($category_id);
        return $this->renderSuccessWeb(compact('list'));
    }
    
    /**
     * 文章分类名
     * @return array
     */
    public function getCategoryName($category_id = 0)
    {
        // 文章分类列表
        $model = new CategoryModel;
        $result = $model::detail($category_id);
        return $this->renderSuccessWeb(compact('result'));
    }
    
    
    /**
     * 获取关于我们
     * @return array
     */
    public function getAboutUsDetail()
    {
        // 文章分类列表
        $CategoryModel = new CategoryModel;
        $model = new ArticleModel;
        $result = $CategoryModel->where('belong',3)->find();
        $detail = "";
        if(!empty($result)){
            $detail = $model->with('image')->where('category_id',$result['category_id'])->find();
        }
        
        
        return $this->renderSuccessWeb(compact('detail'));
    }
    
    
    /**
     * 获取普通文章点分类列表
     * @return array
     */
    public function getNewsCategory()
    {
        // 文章分类列表
        $model = new CategoryModel;
        $list = $model->getNormalList();
        return $this->renderSuccessWeb(compact('list'));
    }
    
    /**
     * 获取普通文章点分类列表
     * @return array
     */
    public function getNewsList($category_id = 0)
    {
        // 文章分类列表
        $model = new ArticleModel;
        $list = $model->getNewsList($category_id);
        return $this->renderSuccessWeb(compact('list'));
    }
    

    /**
     * 文章列表
     * @param int $category_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($category_id = 0)
    {
        $model = new ArticleModel;
        $list = $model->getList($category_id);
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 新手问题
     */
    public function Novice(){
      $model = new ArticleModel;
      $category_id = 10002;
      $list = $model->getList($category_id);
      return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 文章详情
     * @param $article_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function detail($article_id)
    {
        $detail = ArticleModel::detail($article_id);
        return $this->renderSuccessWeb(compact('detail'));
    }
    
    /**
     * 关于我们
     * @param $article_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function about()
    {   
       $param =input();
       $data= (new ArticleModel())->alias('a')-> join('article_category b ','b.category_id= a.category_id','LEFT')
      ->where('a.wxapp_id',$param['wxapp_id'])
       ->where('b.belong',3)
       ->select();
        $detail = $data['0'];
        return $this->renderSuccess(compact('detail'));
    }
    
         // 包裹预报 - 协议
     public function report_note(){
        $category_id = (new CategoryModel())->where(['belong'=>4])->value("category_id");
        $detail = ArticleModel::where(['category_id'=>$category_id])->find();
        return $this->renderSuccess(compact('detail'));
     }
     

}
