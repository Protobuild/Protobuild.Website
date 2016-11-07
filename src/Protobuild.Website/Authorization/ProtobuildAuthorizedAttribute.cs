using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Filters;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Protobuild.Website.Authorization
{
    public class ProtobuildAuthorizedAttribute : Attribute, IActionFilter
    {
        public void OnActionExecuted(ActionExecutedContext context)
        {
            throw new NotImplementedException();
        }

        public void OnActionExecuting(ActionExecutingContext context)
        {
            var session = context.HttpContext.Session;
            if (!session.IsAvailable)
            {
                context.Result = new StatusCodeResult(403);
                return;
            }

            if (session.GetInt32("IsAuthenticated") == 1)
            {
                // We are authenticated - allow request.
                return;
            }

            // TODO: Google Auth here.
            throw new NotImplementedException();
        }
    }
}
