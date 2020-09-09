if ngx.req.get_method() ~= "POST" then
    ngx.log(ngx.ERR, "wrong event request method: ", ngx.req.get_method())
    return ngx.exit (ngx.HTTP_NOT_ALLOWED)
end
-- init modules
local ljs = require("json")

local ck = require "cookie"
local cookie, err = ck:new()
if not cookie then
    ngx.log(ngx.ERR, err)
    return ngx.exit (ngx.HTTP_BAD_REQUEST)
end
local redis = require "redis"
local red = redis:new()

red:set_timeout(1000) -- 1 sec

local ok, err = red:connect("127.0.0.1", 6379)
if not ok then
    ngx.log(ngx.ERR, "failed to connect: ", err)
    return ngx.exit (ngx.HTTP_BAD_REQUEST)
end

local ok, err = red:select(1)
if not ok then
    ngx.log(ngx.ERR, "failed to select: ", err)
    return ngx.exit (ngx.HTTP_BAD_REQUEST)
end
-- 
-- ngx.say("TIME  ", ngx.time())

local data2, err = ngx.req.get_body_data()
if not data2 then
    ngx.log(ngx.ERR, "failed to get request body ", err)
    return ngx.exit (ngx.HTTP_BAD_REQUEST)
end
-- ngx.log(ngx.ERR, "DATA ",data2)
local data,err = ljs.decode(data2)
if not data then
    ngx.log(ngx.ERR, "decode json error")
    return ngx.exit (ngx.HTTP_BAD_REQUEST)
end
-- ngx.log(ngx.ERR, "DATA OK")
if data['sid'] == nil or data['rid'] == nil then
    ngx.log(ngx.ERR, "rid/sid missing")
    return ngx.exit (ngx.HTTP_BAD_REQUEST)
end
-- ngx.say(data['sid'])
-- ngx.say(data['rid'])
-- get all args
-- local qargs = ngx.req.get_uri_args()
-- for k,v in pairs(data['rid']) do
--   ngx.say(k, '=>', v)
-- /presentation/([0-9a-f-]+)/
-- /meetingId=([0-9a-f-]+)
-- end
local ctm = ngx.time()
local sid = data['sid']
for k,v in pairs(data['rid']) do
    	ok, err = red:set("bbbaccess_" .. k .. "_" .. sid, ctm)
end

ngx.say('OK');
