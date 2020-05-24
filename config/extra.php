<?php

return [
    'user_type' => [
        '1'  => '管理员',
        '10' => '仓库管理员'
    ],
    'project_status' => [
        ''  => '全部',
        '1' => '待开工',
        '2' => '已开工',
        '3' => '待结算',
        '4' => '已结算',
    ],
    'stock_type' => [
        ''   => '全部',
        '1'  => '采购入库',
        '5'  => '盘库入库',
        '6'  => '盘库出库',
        '7'  => '领取出库',
        '8'  => '归还入库',
        '9'  => '工程调拨出库',
        '10' => '工程调拨入库',
        '11' => '结余调拨出库',
        '12' => '结余调拨入库',
        '13' => '甲供退还',
        '14' => '工程库存调拨出库',
        '15' => '工程库存调拨入库'
    ],
    'order_status' => [
        '1' => '待审核',
        '2' => '成功',
        '3' => '已取消'
    ],
    'buy_status' => [
        ''  => '全部',
        '1' => '待确认',
        '2' => '采购中',
        '3' => '部分入库',
        '4' => '已完成',
        '5' => '已取消',
        '6' => '申请中',
        '7' => '申请失败',
        '9' => '已作废'
    ],
    'buy_from' => [
        ''  => '全部',
        '1' => '自购',
        '2' => '甲供'
    ],
];
