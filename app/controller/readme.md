# 控制器文件夹



# 项目备注

## 事务的状态 
> 0 create-> 创建中 \
> -1 事务回滚
> 1 add -> 创建依赖中 ->dependency \
> 2 dependency -> 构建中 ->end \
> 3 end->结束 ->Prepare\
> 4 Prepare->预提交 \
> 5 提交 \
> 6 完成


