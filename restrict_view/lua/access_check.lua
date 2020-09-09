if ngx.req.get_method() ~= "GET" then
    ngx.log(ngx.ERR, "wrong event request method: ", ngx.req.get_method())
    return ngx.exit (ngx.HTTP_NOT_ALLOWED)
end

-- init modules

local ck = require "cookie"
local cookie, err = ck:new()
if not cookie then
    ngx.log(ngx.ERR, err)
    return
end
local redis = require "redis"
local red = redis:new()

red:set_timeout(1000) -- 1 sec

local ok, err = red:connect("127.0.0.1", 6379)
if not ok then
    ngx.log(ngx.ERR, "failed to connect: ", err)
    return
end

local ok, err = red:select(1)
if not ok then
    ngx.log(ngx.ERR, "failed select(1) ", err)
    return
end
-- 
local ctm = ngx.time()

-- get url
local uri = ngx.var.request_uri
local rid = nil
local m, err = ngx.re.match(uri, "/meetingId=([0-9a-f-]+)", "io")
if m then
   rid = m[1]
else
   m, err = ngx.re.match(uri, "/presentation/([0-9a-f-]+)/", "io")
   if m then
      rid = m[1]
   end
end
if rid == nil then
   return ngx.HTTP_FORBIDDEN
end
--ngx.log(ngx.ERR, "RID: ", rid)

-- get all cookies
local fields, err = cookie:get_all()
if fields then
  for k, v in pairs(fields) do
    if string.find(k,"^MoodleSession") then
--      ngx.log(ngx.ERR, "C:", k, " SID: ", v)
      local res, err = red:get("bbbaccess_" .. rid .. "_" .. v)
      if res ~= ngx.null then
	if res < ctm - 10000 then
	  return ngx.HTTP_FORBIDDEN
	else
    	  return ngx.HTTP_OK
        end
      end
     end
  end
end
return ngx.HTTP_FORBIDDEN
