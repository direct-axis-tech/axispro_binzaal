var EIDAWebComponentName = "EIDAWebComponent";
var EIDAWebComponent = null;
var RemoteSM_Address = "http://localhost:8080/RemoteSecureMessagingService/JsonSMServlet";

function ReadPublicData(sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation)
{
    //try
    //{
    //PublicDataWebComponent = document.getElementById(PublicDataWebComponentName);
    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.");
        return;
    }

    var Ret = EIDAWebComponent.ReadPublicData(sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation);

    return Ret;
    //}
    //catch(e)
    //{
    //	return "An exception occured when reading public data."+e;
    //}
}

function ReadPublicDataEx(sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation,
                          sReadV2Fields, sReadSignatureImage, sReadAddress)
{
    //try
    //{
    //PublicDataWebComponent = document.getElementById(PublicDataWebComponentName);
    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.");
        return;
    }


    //var Ret = EIDAWebComponent.ReadPublicData(sRefresh, "false", "true", "true", "false");
    var Ret = EIDAWebComponent.ReadPublicDataEx(sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation,
        sReadV2Fields, sReadSignatureImage, sReadAddress);

    return Ret;
    //}
    //catch(e)
    //{
    //	return "An exception occured when reading public data."+e;
    //}
}

function ReadFamilyBookData(sReadAddress)
{
    //try
    //{
    //PublicDataWebComponent = document.getElementById(PublicDataWebComponentName);
    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.");
        return;
    }


    //var Ret = EIDAWebComponent.ReadPublicData(sRefresh, "false", "true", "true", "false");
    var Ret = EIDAWebComponent.ReadFamilyBookData(sReadAddress);

    return Ret;
    //}
    //catch(e)
    //{
    //	return "An exception occured when reading public data."+e;
    //}
}
function ReadPublicDataContactlessWithMRZData(MRZData, sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation, sReadV2Fields, sReadSignatureImage, sReadAddress) {
    if (EIDAWebComponent == null) {
        alert("The Webcomponent is not initialized.");
        return;

    }

    var Ret = EIDAWebComponent.ReadPublicDataContactlessWithMRZData(MRZData, sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation, sReadV2Fields, sReadSignatureImage, sReadAddress);

    return Ret;
}

function ReadPublicDataContactless(CardNumber, DateOfBirth, ExpiryDate, sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation, sReadV2Fields, sReadSignatureImage, sReadAddress) {
    if (EIDAWebComponent == null) {
        alert("The Webcomponent is not initialized.");
        return;
    }

    var Ret = EIDAWebComponent.ReadPublicDataContactless(CardNumber, DateOfBirth, ExpiryDate, sRefresh, sReadPhotography, sReadNonModifiableData, sReadModifiableData, sSignatureValidation, sReadV2Fields, sReadSignatureImage, sReadAddress);

    return Ret;
}


function GetCardSerialNumber(){return EIDAWebComponent.GetCardSerialNumber();}

function GetArabicFullName()
{
    var value = EIDAWebComponent.GetArabicFullName();
    return RemoveCommas(value);
}

///////////////////////
function GetCardSerialNumber(){return EIDAWebComponent.GetCardSerialNumber();}

function GetPhotography_DataSigned(){return EIDAWebComponent.GetPhotography_DataSigned();}
function GetCardHolderData_SF3_DataSigned(){return EIDAWebComponent.GetCardHolderData_SF3_DataSigned();}
function GetCardHolderData_SF5_DataSigned(){return EIDAWebComponent.GetCardHolderData_SF5_DataSigned();}

// Signature used for data validation
function GetPhotography_Signature(){return EIDAWebComponent.GetPhotography_Signature();}
function GetCardHolderData_SF3_Signature(){return EIDAWebComponent.GetCardHolderData_SF3_Signature();}
function GetCardHolderData_SF5_Signature(){return EIDAWebComponent.GetCardHolderData_SF5_Signature();}

// Attributes of the SF 1
function GetIDNumber(){return EIDAWebComponent.GetIDNumber();}
function GetCardNumber(){return EIDAWebComponent.GetCardNumber();}

// Attributes of the SF 2
function GetPhotography(){return EIDAWebComponent.GetPhotography();}

// Attributes of the SF 3
function GetIDType(){return EIDAWebComponent.GetIDType();}
function GetIssueDate(){return EIDAWebComponent.GetIssueDate();}
function GetExpiryDate(){return EIDAWebComponent.GetExpiryDate();}
function GetArabicTitle(){return EIDAWebComponent.GetArabicTitle();}
function GetArabicFullName()
{
    var value = EIDAWebComponent.GetArabicFullName();
    return RemoveCommas(value);
}
function GetTitle(){return EIDAWebComponent.GetTitle();}

function RemoveCommas(value)
{
    if(value==",,")
        return "";

    var fns = value.split(",");

    value = "";
    for(j=0; j<fns.length; j++)
    {
        if(fns[j]=="")
            continue;

        if(value!="")
            value = value + " ";

        value = value + fns[j];
    }

    return value;
}

function GetFullName()
{
    var fullname = EIDAWebComponent.GetFullName();

    return RemoveCommas(fullname);
}

function GetSex()
{

    var sex = EIDAWebComponent.GetSex();
    if(sex == 'M')
        return "Male";
    if(sex == 'F')
        return "Female";
    if(sex == 'X')
        return "Unknown";
}
function GetArabicNationality(){return EIDAWebComponent.GetArabicNationality();}
function GetNationality(){return EIDAWebComponent.GetNationality();}
function GetDateOfBirth(){return EIDAWebComponent.GetDateOfBirth();}
function GetArabicMotherFirstName()
{
    var value = EIDAWebComponent.GetArabicMotherFirstName();
    return RemoveCommas(value);
}

function GetMotherFirstName(){return EIDAWebComponent.GetMotherFirstName();}

// Attributes of the SF 5
function GetOccupation()
{
    var occupation = EIDAWebComponent.GetOccupation();
    return GetOccupationDisplayName(parseInt(occupation));
}
function GetMaritalStatus()
{
    var MaritalStatuses = new Array("", "Single", "Married", "Divorced", "Widowed");
    var maritalStatus = EIDAWebComponent.GetMaritalStatus();

    return MaritalStatuses[parseInt(maritalStatus)];
}
function GetFamilyID(){return EIDAWebComponent.GetFamilyID();}
function GetHusbandIDN(){return EIDAWebComponent.GetHusbandIDN();}
function GetSponsorType()
{
    var SponsorTypes = new Array("Parent", "Spoose", "", "", "Sheikh", "UAE Citizen", "Resident", "GCC Sponsor", "Diplomatic", "Company", "Federal Government", "Local Government", "Assimilated Government", "Heritance", "", "", "", "", "Other Sponsor type");
    var sponsorType = EIDAWebComponent.GetSponsorType();
    return SponsorTypes[parseInt(sponsorType)+3];

}
function GetSponsorNumber()
{
    return EIDAWebComponent.GetSponsorNumber();
}
function GetSponsorName(){return EIDAWebComponent.GetSponsorName();}
function GetResidencyType()
{
    var ResidencyTypes = new Array("", "", "Work", "Resident", "Diplomatic", "", "", "Service");
    var residencyType = EIDAWebComponent.GetResidencyType();
    return ResidencyTypes[parseInt(residencyType)];
}
function GetResidencyNumber(){return EIDAWebComponent.GetResidencyNumber();}
function GetResidencyExpiryDate(){return EIDAWebComponent.GetResidencyExpiryDate();}

// Attributes of the SF 6
function GetMOIRootCertificate(){return EIDAWebComponent.GetMOIRootCertificate();}
//////////////////////


function GetOccupationCode() { return EIDAWebComponent.GetOccupationCode(); }

function GetOccupationEnglish()
{
    return EIDAWebComponent.GetOccupationEnglish();
}

function GetOccupationArabic()
{
    return EIDAWebComponent.GetOccupationArabic();
}

function GetOccupationField()
{
    return EIDAWebComponent.GetOccupationField();
}

function GetPlaceOfBirth()
{
    return EIDAWebComponent.GetPlaceOfBirth();
}

function GetPlaceOfBirth_Ar()
{
    return EIDAWebComponent.GetPlaceOfBirth_Ar();
}

function GetOccupationType()
{
    return EIDAWebComponent.GetOccupationType();
}

function GetOccupationType_Ar()
{
    return EIDAWebComponent.GetOccupationType_Ar();
}

function GetCompanyName()
{
    return EIDAWebComponent.GetCompanyName();
}

function GetCompanyName_Ar()
{
    return EIDAWebComponent.GetCompanyName_Ar();
}

function GetPassportNumber()
{
    return EIDAWebComponent.GetPassportNumber();
}

function GetPassportType()
{
    return EIDAWebComponent.GetPassportType();
}

function GetPassportIssueDate()
{
    return EIDAWebComponent.GetPassportIssueDate();
}

function GetPassportExpiryDate()
{
    return EIDAWebComponent.GetPassportExpiryDate();
}

function GetPassportCountry()
{
    return EIDAWebComponent.GetPassportCountry();
}

function GetPassportCountryDesc()
{
    return EIDAWebComponent.GetPassportCountryDesc();
}

function GetPassportCountryDesc_Ar()
{
    return EIDAWebComponent.GetPassportCountryDesc_Ar();
}

function GetSponsorUnifiedNumber()
{
    return EIDAWebComponent.GetSponsorUnifiedNumber();
}

function GetMotherFullName()
{
    return EIDAWebComponent.GetMotherFullName();
}

function GetMotherFullName_Ar()
{
    return EIDAWebComponent.GetMotherFullName_Ar();
}

function GetDegreeDesc()
{
    return EIDAWebComponent.GetDegreeDesc();
}

function GetDegreeDesc_Ar()
{
    return EIDAWebComponent.GetDegreeDesc_Ar();
}

function GetFieldOfStudy_Code()
{
    return EIDAWebComponent.GetFieldOfStudy_Code()
}

function GetFieldOfStudy()
{
    return EIDAWebComponent.GetFieldOfStudy();
}

function GetFieldOfStudy_Ar()
{
    return EIDAWebComponent.GetFieldOfStudy_Ar()
}

function GetPlaceOfStudy()
{
    return EIDAWebComponent.GetPlaceOfStudy()
}

function GetPlaceOfStudy_Ar()
{
    return EIDAWebComponent.GetPlaceOfStudy_Ar();
}

function GetGraduationDate()
{
    return EIDAWebComponent.GetGraduationDate();
}

function GetQualificationLevel()
{
    return EIDAWebComponent.GetQualificationLevel();
}

function GetQualificationLevelDesc()
{
    return EIDAWebComponent.GetQualificationLevelDesc();
}

function GetQualificationLevelDesc_Ar()
{
    return EIDAWebComponent.GetQualificationLevelDesc_Ar()
}

function GetHolderSignatureImage()
{
    return EIDAWebComponent.GetHolderSignatureImage();
}

function GetHomeAddress_EmirateDesc()
{
    return EIDAWebComponent.GetHomeAddress_EmirateDesc();
}

function GetHomeAddress_EmirateDesc_Ar()
{
    return EIDAWebComponent.GetHomeAddress_EmirateDesc_Ar();
}

function GetHomeAddress_CityDesc()
{
    return EIDAWebComponent.GetHomeAddress_CityDesc();
}

function GetHomeAddress_CityDesc_Ar()
{
    return EIDAWebComponent.GetHomeAddress_CityDesc_Ar();
}

function GetHomeAddress_Street()
{
    return EIDAWebComponent.GetHomeAddress_Street();
}

function GetHomeAddress_Street_Ar()
{
    return EIDAWebComponent.GetHomeAddress_Street_Ar();
}

function GetHomeAddress_AreaDesc()
{
    return EIDAWebComponent.GetHomeAddress_AreaDesc();
}

function GetHomeAddress_AreaDesc_Ar()
{
    return EIDAWebComponent.GetHomeAddress_AreaDesc_Ar();
}

function GetHomeAddress_Building()
{
    return EIDAWebComponent.GetHomeAddress_Building();
}

function GetHomeAddress_Building_Ar()
{
    return EIDAWebComponent.GetHomeAddress_Building_Ar();
}

function GetHomeAddress_MobilePhoneNumber()
{
    return EIDAWebComponent.GetHomeAddress_MobilePhoneNumber();
}

function GetHomeAddress_ResidentPhoneNumber()
{
    return EIDAWebComponent.GetHomeAddress_ResidentPhoneNumber();
}

function  GetHomeAddress_Email()
{
    return EIDAWebComponent.GetHomeAddress_Email();
}

function GetHomeAddress_AddressTypeCode()
{
    return EIDAWebComponent.GetHomeAddress_AddressTypeCode();
}

function GetHomeAddress_LocationCode()
{
    return EIDAWebComponent.GetHomeAddress_LocationCode();
}

function GetHomeAddress_EmirateCode()
{
    return EIDAWebComponent.GetHomeAddress_EmirateCode();
}

function GetHomeAddress_CityCode()
{
    return EIDAWebComponent.GetHomeAddress_CityCode();
}

function GetHomeAddress_POBox()
{
    return EIDAWebComponent.GetHomeAddress_POBox();
}

function GetHomeAddress_AreaCode()
{
    return EIDAWebComponent.GetHomeAddress_AreaCode();
}

function GetHomeAddress_FlatNumber()
{
    return EIDAWebComponent.GetHomeAddress_FlatNumber();
}

function GetWorkAddress_EmirateDesc()
{
    return EIDAWebComponent.GetWorkAddress_EmirateDesc();
}

function GetWorkAddress_EmirateDesc_Ar()
{
    return EIDAWebComponent.GetWorkAddress_EmirateDesc_Ar();
}

function GetWorkAddress_CityDesc()
{
    return EIDAWebComponent.GetWorkAddress_CityDesc();
}

function GetWorkAddress_CityDesc_Ar()
{
    return EIDAWebComponent.GetWorkAddress_CityDesc_Ar();
}

function GetWorkAddress_Street()
{
    return EIDAWebComponent.GetWorkAddress_Street();
}

function GetWorkAddress_Street_Ar()
{
    return EIDAWebComponent.GetWorkAddress_Street_Ar();
}

function GetWorkAddress_AreaDesc()
{
    return EIDAWebComponent.GetWorkAddress_AreaDesc();
}

function GetWorkAddress_AreaDesc_Ar()
{
    return EIDAWebComponent.GetWorkAddress_AreaDesc_Ar();
}

function GetWorkAddress_Building()
{
    return EIDAWebComponent.GetWorkAddress_Building();
}

function GetWorkAddress_Building_Ar()
{
    return EIDAWebComponent.GetWorkAddress_Building_Ar();
}

function GetWorkAddress_MobilePhoneNumber()
{
    return EIDAWebComponent.GetWorkAddress_MobilePhoneNumber();
}

function GetWorkAddress_LandPhoneNumber()
{
    return EIDAWebComponent.GetWorkAddress_LandPhoneNumber();
}

function GetWorkAddress_Email()
{
    return EIDAWebComponent.GetWorkAddress_Email();
}

function GetWorkAddress_AddressTypeCode()
{
    return EIDAWebComponent.GetWorkAddress_AddressTypeCode();
}

function GetWorkAddress_LocationCode()
{
    return EIDAWebComponent.GetWorkAddress_LocationCode();
}

function GetWorkAddress_EmirateCode()
{
    return EIDAWebComponent.GetWorkAddress_EmirateCode();
}

function GetWorkAddress_CityCode()
{
    return EIDAWebComponent.GetWorkAddress_CityCode();
}

function GetWorkAddress_POBox()
{
    return EIDAWebComponent.GetWorkAddress_POBox();
}

function GetWorkAddress_AreaCode()
{
    return EIDAWebComponent.GetWorkAddress_AreaCode();
}

function GetWorkAddress_CompanyName()
{
    return EIDAWebComponent.GetWorkAddress_CompanyName();
}

function GetWorkAddress_CompanyName_Ar()
{
    return EIDAWebComponent.GetWorkAddress_CompanyName_Ar();
}


// FamilyBook getters

function GetFamilyHead_IDN()
{ return EIDAWebComponent.GetFamilyHead_IDN(); }

function GetFamilyHead_FamilyID()
{ return EIDAWebComponent.GetFamilyHead_FamilyID(); }

function GetFamilyHead_EmirateName()
{ return EIDAWebComponent.GetFamilyHead_EmirateName(); }

function GetFamilyHead_EmirateName_Ar()
{ return EIDAWebComponent.GetFamilyHead_EmirateName_Ar(); }

function GetFamilyHead_FirstName()
{ return EIDAWebComponent.GetFamilyHead_FirstName(); }

function GetFamilyHead_FirstName_Ar()
{ return EIDAWebComponent.GetFamilyHead_FirstName_Ar(); }

function GetFamilyHead_FatherName()
{ return EIDAWebComponent.GetFamilyHead_FatherName(); }

function GetFamilyHead_FatherName_Ar()
{ return EIDAWebComponent.GetFamilyHead_FatherName_Ar(); }

function GetFamilyHead_GrandFatherName()
{ return EIDAWebComponent.GetFamilyHead_GrandFatherName(); }

function GetFamilyHead_GrandFatherName_Ar()
{ return EIDAWebComponent.GetFamilyHead_GrandFatherName_Ar(); }

function GetFamilyHead_Tribe()
{ return EIDAWebComponent.GetFamilyHead_Tribe(); }

function GetFamilyHead_Tribe_Ar()
{ return EIDAWebComponent.GetFamilyHead_Tribe_Ar(); }

function GetFamilyHead_Clan()
{ return EIDAWebComponent.GetFamilyHead_Clan(); }

function GetFamilyHead_Clan_Ar()
{ return EIDAWebComponent.GetFamilyHead_Clan_Ar(); }

function GetFamilyHead_NationalityCode()
{ return EIDAWebComponent.GetFamilyHead_NationalityCode(); }

function GetFamilyHead_Nationality()
{ return EIDAWebComponent.GetFamilyHead_Nationality(); }

function GetFamilyHead_Nationality_Ar()
{ return EIDAWebComponent.GetFamilyHead_Nationality_Ar(); }

function GetFamilyHead_Sex()
{ return EIDAWebComponent.GetFamilyHead_Sex(); }

function GetFamilyHead_DateOfBirth()
{ return EIDAWebComponent.GetFamilyHead_DateOfBirth(); }

function GetFamilyHead_PlaceOfBirth()
{ return EIDAWebComponent.GetFamilyHead_PlaceOfBirth(); }

function GetFamilyHead_PlaceOfBirth_Ar()
{ return EIDAWebComponent.GetFamilyHead_PlaceOfBirth_Ar(); }

function GetFamilyHead_MotherFullName()
{ return EIDAWebComponent.GetFamilyHead_MotherFullName(); }

function GetFamilyHead_MotherFullName_Ar()
{ return EIDAWebComponent.GetFamilyHead_MotherFullName_Ar(); }


function GetWife_IDN(wifeIndex)
{
    if(wifeIndex == undefined)
        wifeIndex = 0;
    return EIDAWebComponent.GetWife_IDN(wifeIndex);
}


function GetWife_FullName(wifeIndex)
{
    if(wifeIndex == undefined)
        wifeIndex = 0;
    return EIDAWebComponent.GetWife_FullName(wifeIndex); }

function GetWife_FullName_Ar(wifeIndex)
{
    if(wifeIndex == undefined)
        wifeIndex = 0;
    return EIDAWebComponent.GetWife_FullName_Ar(wifeIndex); }

function GetWife_NationalityCode(wifeIndex)
{
    if(wifeIndex == undefined)
        wifeIndex = 0;
    return EIDAWebComponent.GetWife_NationalityCode(wifeIndex); }

function GetWife_Nationality(wifeIndex)
{
    if(wifeIndex == undefined)
        wifeIndex = 0;
    return EIDAWebComponent.GetWife_Nationality(wifeIndex); }

function GetWife_Nationality_Ar(wifeIndex)
{
    if(wifeIndex == undefined)
        wifeIndex = 0;
    return EIDAWebComponent.GetWife_Nationality_Ar(wifeIndex); }

function GetChild_IDN(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_IDN(childIndex); }

function GetChild_FirstName(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_FirstName(childIndex); }

function GetChild_FirstName_Ar(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_FirstName_Ar(childIndex); }

function GetChild_Sex(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_Sex(childIndex); }

function GetChild_DateOfBirth(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_DateOfBirth(childIndex); }

function GetChild_PlaceOfBirth(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_PlaceOfBirth(childIndex); }

function GetChild_PlaceOfBirth_Ar(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_PlaceOfBirth_Ar(childIndex); }

function GetChild_MotherIDN(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_MotherIDN(childIndex); }

function GetChild_MotherFullName(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_MotherFullName(childIndex); }

function GetChild_MotherFullName_Ar(childIndex)
{
    if(childIndex == undefined)
        childIndex = 0;
    return EIDAWebComponent.GetChild_MotherFullName_Ar(childIndex); }


function StrEndsWithLabel(str)
{
    if(str==null || str==undefined)
        return false;

    return (str.match("_PDLabel$")=="_PDLabel");
}

function GetFunctionName(str)
{
    if(str==null || str==undefined)
        return "undefined";

    return "Get"+str.substring(0, str.length - "_PDLabel".length);
}

function Initialize()
{


    try
    {

        EIDAWebComponent = document.getElementById(EIDAWebComponentName);

        var Ret = EIDAWebComponent.Initialize();

        // alert(Ret);

        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            return "";
        }
        else if(Ret != "")
        {
            alert(Ret);
        }
        else // no errors
        {
            disableButtons(false);
        }

        return Ret;
    }
    catch(e)
    {
        return "Webcomponent Initialization Failed, Details: "+e;
    }
}

function Disconnect()
{
    try
    {

        EIDAWebComponent = document.getElementById(EIDAWebComponentName);

        var Ret = EIDAWebComponent.Disconnect();

        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            return "";
        }
        else if(Ret != "")
        {
            alert(Ret);
        }
        else // no errors
        {
            disableButtons(false);
        }

        return Ret;
    }
    catch(e)
    {
        return "Disconnect Failed, Details: "+e;
    }
}

function InitializeContactless()
{
    try
    {

        EIDAWebComponent = document.getElementById(EIDAWebComponentName);

        var Ret = EIDAWebComponent.InitializeContactless();

        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            return "";
        }
        else if(Ret != "")
        {
            alert(Ret);
        }
        else // no errors
        {
            disableButtons(false);
        }

        return Ret;
    }
    catch(e)
    {
        return "Webcomponent Initialization Failed, Details: "+e;
    }
}

function CheckCardGenuine()
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return;
        }

        disableButtons(true, "Check Card Genuine ...");
        var Ret = EIDAWebComponent.CheckCardGenuine(RemoteSM_Address);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            alert(Ret);
        }
        else
        {
            alert("The Card is genuine");
        }
        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Check Card Genuine Failed, Details: "+e;
    }
}

function CheckCardGenuineEx()
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return;
        }

        disableButtons(true, "Check Card Genuine (Ex)...");
        var Ret = EIDAWebComponent.CheckCardGenuineEx(RemoteSM_Address);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            alert(Ret);
        }
        else
        {
            alert("The Card is genuine");
        }
        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Check Card Genuine Failed, Details: "+e;
    }
}

function ResetPIN(PIN)
{
    disableButtons(true, "PIN Reset  ...");
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "PIN Reset  ...");
        var Ret = EIDAWebComponent.PKIResetPIN(RemoteSM_Address,PIN);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "PIN Reset Failed ,Details: "+e;
    }
}

function ChangePIN (oldPIN,newPIN)
{
    //disableButtons(true, "PIN Change  ...");
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "PIN Change  ...");
        var Ret = EIDAWebComponent.PKIChangePIN(oldPIN, newPIN);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "PIN Change Failed ,Details: "+e;
    }
}

function DisplayPhotography()
{
    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.");
        return;
    }

    try
    {
        disableButtons(true, "Reading Photography ...");

        var Ret = EIDAWebComponent.ReadPublicData("true", "true", "false", "false", "false");
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            flag = true;
        }
        else
        if(Ret != "")
        {
            alert(Ret);
        }

        disableButtons(false);

    }
    catch(e)
    {
        alert("Can not load photography");
    }

}

function DisplayPublicData()
{

    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.Please try again");
        Initialize();
        return;
    }

    Initialize();

    var flag = false;
    disableButtons(true, "Reading Public Data ...");

    // var Ret = ReadPublicData("true", "false", "true", "true", "false");
    // if(Ret.startsWith("-"))
    // {
    //     ProcessError(Ret);
    //     flag = true;
    // }
    // else
    // if(Ret != "")
    // {
    //     alert(Ret);
    // }
    //
    // disableButtons(false);
    //
    // if(flag)
    // {
    //     return;
    // }

    var Ret = ReadPublicDataEx("true", "false", "true", "true", "false", "true", "true", "true");
    if(Ret.startsWith("-"))
    {
        ProcessError(Ret);
        flag = true;
    }
    else
    if(Ret != "")
    {
        alert(Ret);
    }

    disableButtons(false);

    if(flag)
    {
        return;
    }

    var full_name = GetFullName();
    var idNumber = GetIDNumber();
    var mobile_number= GetHomeAddress_MobilePhoneNumber();
    var email_address= GetHomeAddress_Email();
     //var email_address= GetHomeAddress_CityCode();

  if(mobile_number.length==9)
    {
        mobile_number='0'+mobile_number;
    }


    $("#contact_person").val(full_name);
    $("#customer_mobile").val(mobile_number);
    $("#customer_email").val(email_address);
    $("#customer_mobile_company").val(mobile_number);
    loadSubCustomer("1");


    // displaying public data
    var items = document.getElementsByTagName("span");

    for(i=0; i<items.length; i++)
    {
        if(StrEndsWithLabel(items[i].getAttribute("id")))
        {
            try
            {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "()");
                if(v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>"+v+"</b>";
            }
            catch(e)
            {
                alert(e);
                Initialize();
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }

}

function DisplayPublicDataEx()
{
    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.");
        return;
    }

    var flag = false;
    disableButtons(true, "Reading Public Data ...");

    var Ret = ReadPublicDataEx("true", "false", "true", "true", "false", "true", "true", "true");
    if(Ret.startsWith("-"))
    {
        ProcessError(Ret);
        flag = true;
    }
    else
    if(Ret != "")
    {
        alert(Ret);
    }

    disableButtons(false);

    if(flag)
    {
        return;
    }

    try {
        //	display holder signature image
        var imgSignature = document.getElementById("imgHolderSignature");
        var imageBase64 = GetHolderSignatureImage();
        //alert(imageBase64);

        if(imageBase64.length > 0) {
            imgSignature.src = "data:image/tiff;base64," + imageBase64;
            imgSignature.style.display = 'block';
        }
    } catch(e) {alert(e);}

    // displaying public data
    var items = document.getElementsByTagName("span");

    for(i=0; i<items.length; i++)
    {
        if(StrEndsWithLabel(items[i].getAttribute("id")))
        {
            try
            {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "()");
                if(v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>"+v+"</b>";
            }
            catch(e)
            {
                alert(e);
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }

}

function DisplayFamilyBookData()
{
    if(EIDAWebComponent == null)
    {
        alert("The Webcomponent is not initialized.");
        return;
    }

    var flag = false;
    disableButtons(true, "Reading FamilyBook Data ...");

    var Ret = ReadFamilyBookData(RemoteSM_Address);
    if(Ret.startsWith("-"))
    {
        ProcessError(Ret);
        flag = true;
    }
    else
    if(Ret != "")
    {
        alert(Ret);
    }

    disableButtons(false);

    if(flag)
    {
        return;
    }

    // displaying public data
    var items = document.getElementsByTagName("span");

    for(i=0; i<items.length; i++)
    {
        if(StrEndsWithLabel(items[i].getAttribute("id")) && items[i].getAttribute("id").startsWith("FamilyHead"))
        {
            try
            {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "()");
                if(v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>"+v+"</b>";
            }
            catch(e)
            {
                alert(e);
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }

    DisplayChildData(0);
    DisplayWifeData(0);

}

function DisplayChildData(index)
{
    var items = document.getElementsByTagName("span");

    for(i=0; i<items.length; i++)
    {
        if(StrEndsWithLabel(items[i].getAttribute("id")) && items[i].getAttribute("id").startsWith("Child"))
        {
            try
            {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "("+ index +")");
                if(v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>"+v+"</b>";
            }
            catch(e)
            {
                alert(e);
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }
}

function DisplayWifeData(index)
{
    var items = document.getElementsByTagName("span");

    for(i=0; i<items.length; i++)
    {
        if(StrEndsWithLabel(items[i].getAttribute("id")) && items[i].getAttribute("id").startsWith("Wife"))
        {
            try
            {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "("+ index +")");
                if(v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>"+v+"</b>";
            }
            catch(e)
            {
                alert(e);
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }
}


function DisplayPublicDataContactlessWithMRZData(MRZData) {
    if (EIDAWebComponent == null) {
        alert("The Webcomponent is not initialized.");
        return;
    }

    var flag = false;
    disableButtons(true, "Reading Public Data ...");

    var Ret = ReadPublicDataContactlessWithMRZData(MRZData, "true", "true", "true", "true", "true", "true", "true", "true");
    if (Ret.startsWith("-")) {
        ProcessError(Ret);
        flag = true;
    }
    else
    if (Ret != "") {
        alert(Ret);
    }

    disableButtons(false);

    if (flag) {
        return;
    }

    try {
        //	display holder signature image
        var imgSignature = document.getElementById("imgHolderSignature");
        var imageBase64 = GetHolderSignatureImage();
        //alert(imageBase64);

        if(imageBase64.length > 0) {
            imgSignature.src = "data:image/tiff;base64," + imageBase64;
            imgSignature.style.display = 'block';
        }
    } catch(e) {alert(e);}

    // displaying public data
    var items = document.getElementsByTagName("span");

    for (i = 0; i < items.length; i++) {
        if (StrEndsWithLabel(items[i].getAttribute("id"))) {
            try {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "()");
                if (v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>" + v + "</b>";
            }
            catch (e) {
                alert(e);
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }

}

function DisplayPublicDataContactless(CardNumber, DateOfBirth, ExpiryDate) {
    if (EIDAWebComponent == null) {
        alert("The Webcomponent is not initialized.");
        return;
    }

    var flag = false;
    disableButtons(true, "Reading Public Data ...");

    var Ret = ReadPublicDataContactless(CardNumber, DateOfBirth, ExpiryDate, "true", "true", "true", "true", "true", "true", "true", "true");
    if (Ret.startsWith("-")) {
        ProcessError(Ret);
        flag = true;
    }
    else
    if (Ret != "") {
        alert(Ret);
    }

    disableButtons(false);

    if (flag) {
        return;
    }

    try {
        //	display holder signature image
        var imgSignature = document.getElementById("imgHolderSignature");
        var imageBase64 = GetHolderSignatureImage();
        //alert(imageBase64);

        if(imageBase64.length > 0) {
            imgSignature.src = "data:image/tiff;base64," + imageBase64;
            imgSignature.style.display = 'block';
        }
    } catch(e) {alert(e);}

    // displaying public data
    var items = document.getElementsByTagName("span");

    for (i = 0; i < items.length; i++) {
        if (StrEndsWithLabel(items[i].getAttribute("id"))) {
            try {
                var v = eval(GetFunctionName(items[i].getAttribute("id")) + "()");
                if (v == null || v == undefined || v.length == 0)
                    v = "--";

                items[i].innerHTML = "<b>" + v + "</b>";
            }
            catch (e) {
                alert(e);
                items[i].innerHTML = "<b>--</b>";
            }
        }
    }

}




function disableButtons(flag, msg)
{

    try
    {

        document.getElementById("btnInitialize").disabled = flag;
        document.getElementById("btnReadPublicData").disabled = flag;
        document.getElementById("btnReadPublicDataEx").disabled = flag;
        document.getElementById("btnCheckCardGenuine").disabled = flag;
        document.getElementById("btnCheckCardGenuineEx").disabled = flag;
        document.getElementById("btnReadPhotography").disabled = flag;
        document.getElementById("btnBiometricVerification").disabled = flag;
    }
    catch(e)
    {

    }

    try
    {
        document.getElementById("btnExportSignCertificate").disabled = flag;
        document.getElementById("btnExportAuthCertificate").disabled = flag;
        document.getElementById("btnSignData").disabled = flag;
        document.getElementById("btnSignDataCSP").disabled = flag;
        document.getElementById("btnResetPIN").disabled=flag;
        document.getElementById("btnChangePIN").disabled=flag;
    }
    catch(e)
    {

    }

    try {
        document.getElementById("btnReadFB").disabled = flag;
    }
    catch(e) {

    }


    if(flag == true)
    {
        document.getElementById("loading_data").innerHTML = msg;
        document.getElementById("loading_data").style.visibility = "visible";

    }
    else
    {
        document.getElementById("loading_data").style.visibility = "hidden";
    }


}

function MatchOffCard()
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return;
        }

        disableButtons(true, "Biometric Verification ...");

        var firstIndex = EIDAWebComponent.GetFirstFingerIndex();
        var secondIndex = EIDAWebComponent.GetSecondFingerIndex();
        var firstFinger = GetFingerIndexDisplayName(firstIndex);
        var secondFinger = GetFingerIndexDisplayName(secondIndex);

        var Time_Out=10;
        var alertMessage = "Please place on of the following fingers on the sensor \n" + firstFinger + "\n" + secondFinger;
        //for(var i=0;i<NumOfBits;i++){
        //	fingerIndex = PublicDataWebComponent.GetFingerIndex(i+1);
        //	if(i!= 0)
        //		alertMessage += " or your ";
        //	alertMessage += GetFingerIndexDisplayName(fingerIndex)+" finger";
        //}
        alertMessage += "\n to capture your fingerprint.";
        alert (alertMessage);

        var image = EIDAWebComponent.CaptureImage(0, firstIndex, Time_Out);
        if(image.startsWith("-"))
        {
            ProcessError(image);
            disableButtons(false);
            return;
        }

        var template = EIDAWebComponent.ConvertImage(image, EIDAWebComponent.GetImageWidth(), EIDAWebComponent.GetImageHeight());
        if(template.startsWith("-"))
        {
            ProcessError(template);
            disableButtons(false);
            return;
        }

        var Ret = EIDAWebComponent.MatchOffCard(RemoteSM_Address, firstIndex, template);

        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else if(Ret != "")
        {
            alert(Ret);
        }
        else
        {
            alert("Biometric verification successful");
        }
        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Biometric verification failed ,Details  " + e;
    }

}


//function SignDataCSP(Data)
//{
//	alert("Signing with SIGN key does not allow PIN caching");
//}

//function AuthenticateCSP(Data)
function AuthenticatePinCached(Data)
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Signing Data with the CSP (Auth KeyPair) ...");
        var Ret = EIDAWebComponent.AuthenticateWithPinCached(Data);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            //	alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Signing Data with the CSP (Auth KeyPair) Failed, Details: "+e;
    }
}


function SignData(PIN, Data)
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Signing Data without PIN caching (Sign KeyPair) ...");
        var Ret = EIDAWebComponent.SignData(PIN, Data);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            //	alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Signing Data without PIN caching (Sign KeyPair) Failed, Details: "+e;
    }
}

function BioAuthenticate()
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Performing Biometric Authentication ...");
        var Ret = EIDAWebComponent.BioAuthenticate(RemoteSM_Address);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            return null;
        }
        else
        if(Ret != "")
        {
            //	alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return null;
    }
}

function Authenticate(PIN, Data)
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Signing Data without PIN caching (Auth KeyPair) ...");
        var Ret = EIDAWebComponent.Authenticate(PIN, Data);
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            //	alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Signing Data without PIN caching (Auth KeyPair) Failed, Details: "+e;
    }
}


function ExportSignCertificate()
{

    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Export Signing Certificate ...");
        var Ret = EIDAWebComponent.ReadSignCertificate();
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            //	alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Export Signing Certificate Failed, Details: "+e;
    }
}

function ExportAuthCertificate()
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Export Auth Certificate ...");
        var Ret = EIDAWebComponent.ReadAuthCertificate();
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            //return "";
        }
        else
        if(Ret != "")
        {
            //	alert(Ret);
        }

        disableButtons(false);
        return Ret;
    }
    catch(e)
    {
        return "Export Auth Certificate Failed, Details: "+e;
    }
}


function VGAuthenticate(PIN, JSCallBackFunctionName)
{
    try
    {
        if(EIDAWebComponent == null)
        {
            alert("The Webcomponent is not initialized.");
            return "";
        }

        disableButtons(true, "Authentication in progress ...");
        EIDAWebComponent.setVGAddress(RemoteSM_Address);
        var Ret = EIDAWebComponent.VGAuthenticate(PIN, JSCallBackFunctionName);
        //alert(Ret);
        document.getElementById("txtResponse").value = Ret;
        if(Ret.startsWith("-"))
        {
            ProcessError(Ret);
            return "";
        }
        else
        if(Ret != "")
        {
            window.location = "Success.html";
        }

        window.location = "Failed.html";

        disableButtons(false);
        //return Ret;
    }
    catch(e)
    {
        return "";//"VG Authenticate Failed, Details: "+e;
    }
}
