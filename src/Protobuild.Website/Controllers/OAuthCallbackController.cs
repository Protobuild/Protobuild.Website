using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;

namespace Protobuild.Website.Controllers
{
    public class OAuthCallbackController : Controller
    {
        [Route("/oauth2callback")]
        public IActionResult Index()
        {
            var url = HttpContext.Session.GetString("ReturnUrl");
            HttpContext.Session.Remove("ReturnUrl");
            return Redirect(url);
        }
    }
}
