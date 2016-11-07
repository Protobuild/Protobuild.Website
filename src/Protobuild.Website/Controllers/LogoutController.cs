using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Authorization;

namespace Protobuild.Website.Controllers
{
    public class LogoutController : Controller
    {
        [ProtobuildAuthorized]
        [Route("/logout")]
        public IActionResult Index()
        {
            HttpContext.Session.Clear();
            return Redirect("/");
        }
    }
}
