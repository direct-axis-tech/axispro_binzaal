var FingerName = new Array();
FingerName[0x05] = "Right Thumb";
FingerName[0x09] = "Right Index";
FingerName[0x0D] = "Right Middle";
FingerName[0x11] = "Right Ring";
FingerName[0x15] = "Right Little";

FingerName[0x06] = "Left Thumb";
FingerName[0x0A] = "Left Index";
FingerName[0x0E] = "Left Middle";
FingerName[0x12] = "Left Ring";
FingerName[0x16] = "Left Little";

function GetFingerIndexDisplayName(fingerIndex){
    return FingerName[fingerIndex];
}