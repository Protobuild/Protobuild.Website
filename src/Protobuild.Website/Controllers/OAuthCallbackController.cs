using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Authorization;

namespace Protobuild.Website.Controllers
{
    public class OAuthCallbackController : Controller
    {
        [ProtobuildAuthorized]
        [Route("/oauth2callback")]
        public IActionResult Index()
        {
            var url = HttpContext.Session.GetString("ReturnUrl");
            HttpContext.Session.Remove("ReturnUrl");
            return Redirect(url);
        }
    }
}
