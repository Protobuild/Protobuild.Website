using Microsoft.AspNetCore.Mvc;

namespace Protobuild.Website.Controllers
{
    public class AccountController : Controller
    {
        [Route("/{user}")]
        public IActionResult Index(string user)
        {
            return View();
        }

        [Route("/{user}/rename")]
        public IActionResult Rename(string user)
        {
            return View();
        }

        [Route("/{user}/owner/add")]
        public IActionResult OwnershipAdd(string user)
        {
            return View();
        }

        [Route("/{user}/owner/remove")]
        public IActionResult OwnershipRemove(string user, string owner)
        {
            return View();
        }
    }
}
