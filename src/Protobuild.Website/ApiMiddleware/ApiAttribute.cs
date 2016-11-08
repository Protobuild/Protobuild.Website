using System;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Filters;

namespace Protobuild.Website.ApiMiddleware
{
    public class ApiAttribute : Attribute, IActionFilter
    {
        public void OnActionExecuting(ActionExecutingContext context)
        {
        }

        public void OnActionExecuted(ActionExecutedContext context)
        {
            if (context.Exception != null)
            {
                var result = new JsonResult(new
                {
                    has_error = true,
                    error = context.Exception.ToString(),
                    result = (object)null
                });

                context.Result = result;
                context.ExceptionHandled = true;
                return;
            }

            var jsonResult = context.Result as JsonResult;

            if (jsonResult != null)
            {
                var result = new JsonResult(new
                {
                    has_error = false,
                    error = (string)null,
                    result = jsonResult.Value
                });

                context.Result = result;
            }
        }
    }
}
